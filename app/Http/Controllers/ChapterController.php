<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterAttachment;
use App\Models\Course;
use App\Models\MuxData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChapterController extends Controller
{
    public function create(Request $request, $courseId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($request->has('title')) {
                    $title = $request->input('title');
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $lastChapter = Chapter::where('courseId', $courseOwner->id)->orderBy('position', 'desc')->first();
                    $newPosition = $lastChapter ? $lastChapter->position + 1 : 1;

                    $chapter = Chapter::create([
                        "title" => $title,
                        "courseId" => $courseOwner->id,
                        "position" => $newPosition,
                        "duration" => 0
                    ]);

                    return response()->json($chapter);
                } else {
                    return response("Title is missing", 400);
                }
            } else {
                return response("Not Found", 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                if ($request->has('title')) {
                    $title = $request->input('title');
                    $course = Course::where('id', $courseId)->first();
                    if (!$course) {
                        return response("Not Found", 404);
                    }
                    $lastChapter = Chapter::where('courseId', $course->id)->orderBy('position', 'desc')->first();
                    $newPosition = $lastChapter ? $lastChapter->position + 1 : 1;

                    $chapter = Chapter::create([
                        "title" => $title,
                        "courseId" => $course->id,
                        "position" => $newPosition,
                        "duration" => 0
                    ]);

                    return response()->json($chapter);
                } else {
                    return response("Title is missing", 400);
                }
            } else {
                return response("Not Found", 404);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function reorder(Request $request, $courseId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($request->has('list')) {
                    $list = $request->input('list');
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    foreach ($list as $item) {
                        Chapter::where('id', $item['id'])->where('courseId', $courseOwner->id)->update(['position' => $item['position']]);
                    }
                    return response("Success", 200);
                } else {
                    return response('List is missing', 400);
                }
            } else {
                return response('Not Found', 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                if ($request->has('list')) {
                    $list = $request->input('list');
                    $course = Course::where('id', $courseId)->first();
                    if (!$course) {
                        return response("Not Found", 404);
                    }
                    foreach ($list as $item) {
                        Chapter::where('id', $item['id'])->where('courseId', $course->id)->update(['position' => $item['position']]);
                    }
                    return response("Success", 200);
                } else {
                    return response('List is missing', 400);
                }
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function set(Request $request, $courseId, $chapterId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($chapterId) {
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $chapter = Chapter::where('id', $chapterId)->where('courseId', $courseOwner->id)->first();
                    if (!$chapter) {
                        return response("Unauthorized", 401);
                    }
                    $chapter->update($request->all());
                    return response()->json($chapter, 200);
                } else {
                    return response('Not Found', 404);
                }
            } else {
                return response('Not Found', 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                if ($chapterId) {
                    $course = Course::where('id', $courseId)->first();
                    if (!$course) {
                        return response("Not Found", 404);
                    }
                    $chapter = Chapter::where('id', $chapterId)->where('courseId', $course->id)->first();
                    if (!$chapter) {
                        return response("Not Found", 404);
                    }
                    $chapter->update($request->all());
                    return response()->json($chapter, 200);
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

    public function delete($courseId, $chapterId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($chapterId) {
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $chapter = Chapter::where('id', $chapterId)->where('courseId', $courseOwner->id)->with(['lectures' => function ($query) {
                        $query->with(['muxData']);
                    }, 'attachments'])->first();
                    if (!$chapter) {
                        return response("Unauthorized", 401);
                    }
                    if ($chapter->lectures) {
                        foreach ($chapter->lectures as $lecture) {
                            if ($lecture->muxData) {
                                $existingMuxData = MuxData::where('lectureId', $lecture->id)->first();
                                if ($existingMuxData) {
                                    $existingMuxData->delete();
                                }
                            }
                        }
                    }
                    if ($chapter->attachments) {
                        foreach ($chapter->attachments as $attachment) {
                            $existingAttachment = ChapterAttachment::where('id', $attachment->id);
                            if ($existingAttachment) {
                                $existingAttachment->delete();
                            }
                        }
                    }
                    $chapter->delete();
                    $publishedChaptersInCourse = Chapter::where('courseId', $courseOwner->id)->where('isPublished', true)->get();
                    if ($publishedChaptersInCourse->isEmpty()) {
                        Course::where('id', $courseOwner->id)->where('userId', $user->id)->update(['isPublished' => false]);
                    }
                    return response()->json($chapter, 200);
                } else {
                    return response('Not Found', 404);
                }
            } else {
                return response('Not Found', 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                if ($chapterId) {
                    $course = Course::where('id', $courseId)->first();
                    if (!$course) {
                        return response("Not Found", 404);
                    }
                    $chapter = Chapter::where('id', $chapterId)->where('courseId', $course->id)->with(['lectures' => function ($query) {
                        $query->with(['muxData']);
                    }, 'attachments'])->first();
                    if (!$chapter) {
                        return response("Not Found", 404);
                    }
                    if ($chapter->lectures) {
                        foreach ($chapter->lectures as $lecture) {
                            if ($lecture->muxData) {
                                $existingMuxData = MuxData::where('lectureId', $lecture->id)->first();
                                if ($existingMuxData) {
                                    $existingMuxData->delete();
                                }
                            }
                        }
                    }
                    if ($chapter->attachments) {
                        foreach ($chapter->attachments as $attachment) {
                            $existingAttachment = ChapterAttachment::where('id', $attachment->id);
                            if ($existingAttachment) {
                                $existingAttachment->delete();
                            }
                        }
                    }
                    $chapter->delete();
                    $publishedChaptersInCourse = Chapter::where('courseId', $course->id)->where('isPublished', true)->get();
                    if ($publishedChaptersInCourse->isEmpty()) {
                        Course::where('id', $course->id)->update(['isPublished' => false]);
                    }
                    return response()->json($chapter, 200);
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

    public function publish($courseId, $chapterId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($chapterId) {
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $chapter = Chapter::where('id', $chapterId)->where('courseId', $courseOwner->id)->with(['lectures'])->first();
                    if (!$chapter) {
                        return response("Unauthorized", 401);
                    }
                    if (!$chapter || !$chapter->title || !$chapter->description || !$chapter->lectures->contains('isPublished', true)) {
                        return response('Missing required fields', 400);
                    }
                    $chapter->update(['isPublished' => true]);
                    return response()->json($chapter, 200);
                } else {
                    return response('Not Found', 404);
                }
            } else {
                return response('Not Found', 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                if ($chapterId) {
                    $course = Course::where('id', $courseId)->first();
                    if (!$course) {
                        return response("Not Found", 404);
                    }
                    $chapter = Chapter::where('id', $chapterId)->where('courseId', $course->id)->with(['lectures'])->first();
                    if (!$chapter) {
                        return response("Not Found", 404);
                    }
                    if (!$chapter || !$chapter->title || !$chapter->description || !$chapter->lectures->contains('isPublished', true)) {
                        return response('Missing required fields', 400);
                    }
                    $chapter->update(['isPublished' => true]);
                    return response()->json($chapter, 200);
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

    public function unpublish($courseId, $chapterId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($chapterId) {
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $chapter = Chapter::where('id', $chapterId)->where('courseId', $courseOwner->id)->with(['lectures'])->first();
                    if (!$chapter) {
                        return response("Unauthorized", 401);
                    }
                    $chapter->update(['isPublished' => false]);
                    $publishedChaptersInCourse = Chapter::where('courseId', $courseOwner->id)->where('isPublished', true)->get();
                    if ($publishedChaptersInCourse->isEmpty()) {
                        Course::where('id', $courseOwner->id)->where('userId', $user->id)->update(['isPublished' => false]);
                    }
                    return response()->json($chapter, 200);
                } else {
                    return response('Not Found', 404);
                }
            } else {
                return response('Not Found', 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                if ($chapterId) {
                    $course = Course::where('id', $courseId)->first();
                    if (!$course) {
                        return response("Not Found", 404);
                    }
                    $chapter = Chapter::where('id', $chapterId)->where('courseId', $course->id)->with(['lectures'])->first();
                    if (!$chapter) {
                        return response("Not Found", 404);
                    }
                    $chapter->update(['isPublished' => false]);
                    $publishedChaptersInCourse = Chapter::where('courseId', $course->id)->where('isPublished', true)->get();
                    if ($publishedChaptersInCourse->isEmpty()) {
                        Course::where('id', $course->id)->update(['isPublished' => false]);
                    }
                    return response()->json($chapter, 200);
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
}
