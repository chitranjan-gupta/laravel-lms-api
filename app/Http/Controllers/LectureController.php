<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lecture;
use App\Models\LectureAttachment;
use App\Models\MuxData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MuxPhp;
use GuzzleHttp;
use Illuminate\Support\Facades\Log;

class LectureController extends Controller
{
    public function create(Request $request, $courseId, $chapterId)
    {
        if (Auth::user()) {
            if ($courseId) {
                if ($chapterId) {
                    $user = Auth::user();
                    if ($request->has('title')) {
                        $title = $request->input("title");
                        $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                        if (!$courseOwner) {
                            return response("Unauthorized", 401);
                        }
                        $chapterOwner = Chapter::find([$chapterId]);
                        if (!$chapterOwner) {
                            return response("Unauthorized", 401);
                        }
                        $lastLecture = Lecture::where('chapterId', $chapterId)->orderBy('position', 'desc')->first();
                        $newPosition = $lastLecture ? $lastLecture->position + 1 : 1;
                        $lecture = Lecture::create([
                            "title" => $title,
                            "courseId" => $courseId,
                            "chapterId" => $chapterId,
                            "position" => $newPosition,
                            "duration" => 0
                        ]);
                        return response()->json($lecture, 200);
                    } else {
                        return response("Title is missing", 400);
                    }
                } else {
                    return response("Not Found", 404);
                }
            } else {
                return response("Not Found", 404);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function set(Request $request, $courseId, $chapterId, $lectureId)
    {
        if (Auth::user()) {
            if ($courseId) {
                if ($chapterId) {
                    if ($lectureId) {
                        $user = Auth::user();
                        $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                        if (!$courseOwner) {
                            return response("Unauthorized", 401);
                        }
                        $chapterOwner = Chapter::where('id', $chapterId)->where('courseId', $courseId)->first();
                        if (!$chapterOwner) {
                            return response("Unauthorized", 401);
                        }
                        $lecture = Lecture::where('id', $lectureId)->where('chapterId', $chapterId)->where('courseId', $courseId)->first();
                        if (!$lecture) {
                            return response("Unauthorized", 401);
                        }
                        $lecture->update($request->all());
                        if ($request->has('videoUrl')) {
                            $videoUrl = $request->input('videoUrl');
                            $existingMuxData = MuxData::where('lectureId', $lectureId)->first();
                            if ($existingMuxData) {
                                MuxData::where('id', $existingMuxData->id)->delete();
                            }
                            // Authentication Setup
                            $muxConfig = MuxPhp\Configuration::getDefaultConfiguration()
                                ->setUsername(getenv('MUX_TOKEN_ID'))
                                ->setPassword(getenv('MUX_TOKEN_SECRET'));

                            // API Client Initialization
                            $assetsApi = new MuxPhp\Api\AssetsApi(
                                new GuzzleHttp\Client(),
                                $muxConfig
                            );

                            // Create Asset Request
                            $input = new MuxPhp\Models\InputSettings(["url" => $videoUrl]);
                            $createAssetRequest = new MuxPhp\Models\CreateAssetRequest(["input" => $input, "playback_policy" => [MuxPhp\Models\PlaybackPolicy::_PUBLIC]]);

                            // Ingest
                            $result = $assetsApi->createAsset($createAssetRequest);

                            if ($result) {
                                $assetId = $result->getData()->getId();
                                $playbackId = $result->getData()->getPlaybackIds()[0]->getId();
                                if ($assetId && $playbackId) {
                                    MuxData::create([
                                        'lectureId' => $lectureId,
                                        'assetId' => $assetId,
                                        'playbackId' => $playbackId
                                    ]);
                                }
                            }
                        }
                        return response()->json($lecture, 200);
                    } else {
                        return response('Not Found', 404);
                    }
                } else {
                    return response('Not Found', 404);
                }
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function delete($courseId, $chapterId, $lectureId)
    {
        if (Auth::user()) {
            if ($courseId) {
                if ($chapterId) {
                    if ($lectureId) {
                        $user = Auth::user();
                        $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                        if (!$courseOwner) {
                            return response("Unauthorized", 401);
                        }
                        $chapterOwner = Chapter::where('id', $chapterId)->where('courseId', $courseId)->first();
                        if (!$chapterOwner) {
                            return response("Unauthorized", 401);
                        }
                        $lecture = Lecture::where('id', $lectureId)->where('chapterId', $chapterId)->where('courseId', $courseId)->with(['muxData', 'attachments'])->first();
                        if (!$lecture) {
                            return response("Unauthorized", 401);
                        }
                        if ($lecture->muxData) {
                            $existingMuxData = MuxData::where('lectureId', $lectureId)->first();
                            if ($existingMuxData) {
                                $existingMuxData->delete();
                            }
                        }
                        if($lecture->attachments){
                            foreach($lecture->attachments as $attachment){
                                $existingAttachment = LectureAttachment::where('id', $attachment->id);
                                if($existingAttachment){
                                    $existingAttachment->delete();
                                }
                            }
                        }
                        $lecture->delete();
                        $publishedLecturesInChapter = Lecture::where('chapterId', $chapterId)->where('isPublished', true)->get();
                        if ($publishedLecturesInChapter->isEmpty()) {
                            Chapter::where('id', $chapterId)->update(['isPublished' => false]);
                        }
                        return response()->json($lecture, 200);
                    } else {
                        return response('Not Found', 404);
                    }
                } else {
                    return response('Not Found', 404);
                }
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function publish($courseId, $chapterId, $lectureId)
    {
        if (Auth::user()) {
            if ($courseId) {
                if ($chapterId) {
                    if ($lectureId) {
                        $user = Auth::user();
                        $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                        if (!$courseOwner) {
                            return response("Unauthorized", 401);
                        }
                        $chapterOwner = Chapter::where('id', $chapterId)->where('courseId', $courseId)->first();
                        if (!$chapterOwner) {
                            return response("Unauthorized", 401);
                        }
                        $lecture = Lecture::where('id', $lectureId)->where('chapterId', $chapterId)->where('courseId', $courseId)->first();
                        if (!$lecture) {
                            return response("Unauthorized", 401);
                        }
                        $muxData = MuxData::where('lectureId', $lectureId)->first();
                        if (!$lecture || !$lecture->title || !$lecture->description || !$lecture->videoUrl || !$muxData) {
                            return response("Missing required fields", 400);
                        }
                        $lecture->update(['isPublished' => true]);
                        return response()->json($lecture);
                    } else {
                        return response('Not Found', 404);
                    }
                } else {
                    return response('Not Found', 404);
                }
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function unpublish($courseId, $chapterId, $lectureId)
    {
        if (Auth::user()) {
            if ($courseId) {
                if ($chapterId) {
                    if ($lectureId) {
                        $user = Auth::user();
                        $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                        if (!$courseOwner) {
                            return response("Unauthorized", 401);
                        }
                        $chapterOwner = Chapter::where('id', $chapterId)->where('courseId', $courseId)->first();
                        if (!$chapterOwner) {
                            return response("Unauthorized", 401);
                        }
                        $lecture = Lecture::where('id', $lectureId)->where('chapterId', $chapterId)->where('courseId', $courseId)->first();
                        if (!$lecture) {
                            return response("Unauthorized", 401);
                        }
                        $lecture->update(['isPublished' => false]);
                        $publishedLecturesInChapter = Lecture::where('chapterId', $chapterId)->where('isPublished', true)->get();
                        if ($publishedLecturesInChapter->isEmpty()) {
                            Log::info("Unpublishing chapter");
                            Chapter::where('id', $chapterId)->update(['isPublished' => false]);
                        }
                        return response()->json($lecture);
                    } else {
                        return response('Not Found', 404);
                    }
                } else {
                    return response('Not Found', 404);
                }
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function reorder(Request $request, $courseId, $chapterId)
    {
        if (Auth::user()) {
            if ($courseId) {
                if ($chapterId) {
                    if ($request->has('list')) {
                        $user = Auth::user();
                        $list = $request->input('list');
                        $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                        if (!$courseOwner) {
                            return response("Unauthorized", 401);
                        }
                        $chapterOwner = Chapter::where('id', $chapterId)->where('courseId', $courseId)->first();
                        if (!$chapterOwner) {
                            return response("Unauthorized", 401);
                        }
                        foreach ($list as $item) {
                            Lecture::where('id', $item['id'])->update(['position' => $item['position']]);
                        }
                        return response("Success", 200);
                    } else {
                        return response("List is missing", 400);
                    }
                } else {
                    return response("Not Found", 404);
                }
            } else {
                return response("Not Found", 404);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }
}
