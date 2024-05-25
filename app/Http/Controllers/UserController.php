<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterAttachment;
use App\Models\ChapterProgress;
use App\Models\Course;
use App\Models\Lecture;
use App\Models\LectureAttachment;
use App\Models\LectureProgress;
use App\Models\MuxData;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function userProfile()
    {
        if (Auth::user()) {
            $user = Auth::user();
            try {
                return response()->json(["userId" => $user->id, "email" => $user->email, "name" => $user->name, "role" => $user->role], 200);
            } catch (\Throwable $e) {
                return response($e->getMessage(), 401);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($request->has('userId') && $request->has('courseId')) {
                $userId = $request->input('userId');
                $courseId = $request->input('courseId');
                $course = Course::where('id', $courseId)
                    ->where('userId', $userId)
                    ->with(['chapters' => function ($query) {
                        $query
                            ->orderBy('position', 'asc');
                    }, 'attachments' => function ($query) {
                        $query
                            ->orderBy('created_at', 'desc');
                    }])->first();
                return response()->json($course, 200);
            } else if ($request->has('userId')) {
                $userId = $request->input('userId');
                $course = Course::where('userId', $userId)->orderBy('created_at', 'desc')->get();
                return response()->json($course, 200);
            }
            return response("Not Found", 404);
        } else if ($user && $user->role == "admin") {
            if ($request->has('courseId')) {
                $courseId = $request->input('courseId');
                $course = Course::where('id', $courseId)
                    ->with(['chapters' => function ($query) {
                        $query
                            ->orderBy('position', 'asc');
                    }, 'attachments' => function ($query) {
                        $query
                            ->orderBy('created_at', 'desc');
                    }])->first();
                return response()->json($course, 200);
            }else{
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function chapter(Request $request)
    {
        if ($request->has("userId") && $request->has("courseId") && $request->has("chapterId")) {
            $courseId = $request->input('courseId');
            $chapterId = $request->input('chapterId');
            $chapter = Chapter::where("id", $chapterId)->where("courseId", $courseId)
                ->with(['lectures' => function ($query) {
                    $query->orderBy('position', 'asc');
                }, 'attachments' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }])->first();
            return response()->json($chapter, 200);
        } else if ($request->has('userId') && $request->has('courseId')) {
            $courseId = $request->input('courseId');
            $publishedChapters = Chapter::where('courseId', $courseId)->where('isPublished', true)->get();
            return response()->json($publishedChapters, 200);
        }
        return response("Not Found", 404);
    }

    public function course(Request $request)
    {
        if (($request->has('userId') && $request->has('title')) || $request->has('categoryId')) {
            $courses = Course::where('isPublished', true)->when($request->has('title'), function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->input('title') . '&');
            })->when($request->has('categoryId'), function ($query) use ($request) {
                $query->where('categoryId', $request->input('categoryId'));
            })->with(['category', 'chapters' => function ($query) {
                $query
                    ->where('isPublished', true);
            }, 'purchases' => function ($query) use ($request) {
                $query->where('userId', $request->input('userId'));
            }])
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($courses, 200);
        } else if ($request->has('userId')) {
            $courses = Course::where('isPublished', true)->with(['category', 'chapters' => function ($query) {
                $query
                    ->where('isPublished', true);
            }, 'purchases' => function ($query) use ($request) {
                $query->where('userId', $request->input('userId'));
            }])
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($courses, 200);
        }
        return response("Not Found", 404);
    }

    public function lecture(Request $request)
    {
        if ($request->has('courseId') && $request->has('chapterId') && $request->has('purchase')) {
            $purchase = null;
            $courseId = $request->input('courseId');
            $chapterId = $request->input('chapterId');
            $lectureId = $request->input('lectureId');
            if ($request->has('userId')) {
                $userId = $request->input('userId');
                global $purchase;
                $purchase = Purchase::where('userId', $userId)->where('courseId', $courseId)->first();
            }
            $course = Course::where('isPublished', true)->where('id', $courseId)->select('price')->first();
            if (!$course) {
                return response("Course not found", 404);
            }
            $chapter = Chapter::where('id', $chapterId)
                ->where('isPublished', true)
                ->with(['lectures' => function ($query) {
                    $query->where('isPublished', true);
                }])->first();
            if (!$chapter) {
                return response("Course not found", 404);
            }
            $lecture = Lecture::where('id', $lectureId)->where('isPublished', true)->first();
            if (!$lecture) {
                return response("Course not found", 404);
            }
            $muxData = null;
            $lectureAttachment = null;
            $chapterAttachment = null;
            $nextChapter = null;
            $nextLecture = null;
            if ($purchase) {
                global $lectureAttachment, $chapterAttachment;
                $lectureAttachment = LectureAttachment::where('id', $lectureId)->get();
                $chapterAttachment = ChapterAttachment::where('id', $chapterId)->get();
            }

            if ($lecture->isFree || $purchase) {
                global $muxData, $nextLecture, $nextChapter;
                $muxData = MuxData::where('lectureId', $lectureId)->first();

                $nextLecture = Lecture::where('courseId', $courseId)
                    ->where('chapterId', $chapter->id)
                    ->where('isPublished', true)
                    ->where('position', '>', $lecture->position ?? 0)
                    ->orderBy('position', 'asc')
                    ->first();

                $nextChapter = Chapter::where('courseId', $courseId)
                    ->where('isPublished', true)
                    ->where('position', '>', $chapter->position ?? 0)
                    ->orderBy('position', 'asc')
                    ->first();

                if ($nextChapter && !$nextLecture) {
                    $nextLecture = Lecture::where('courseId', $courseId)
                        ->where('chapterId', $nextChapter->id)
                        ->where('isPublished', true)
                        ->orderBy('position', 'asc')
                        ->first();
                }
            }

            $chapterProgress = null;
            $lectureProgress = null;

            if ($request->has('userId')) {
                $userId = $request->input('userId');
                global $chapterProgress, $lectureProgress;

                $chapterProgress = ChapterProgress::where('userId', $userId)->where('chapterId', $chapterId)->first();

                $lectureProgress = LectureProgress::where('userId', $userId)->where('lectureId', $lectureId)->first();
            }

            return response()->json(["lecture" => $lecture, "chapter" => $chapter, "course" => $course, "muxData" => $muxData, "lectureAttachments" => $lectureAttachment, "chapterAttachments" => $chapterAttachment, "nextChapter" => $nextChapter, "nextLecture" => $nextLecture, "chapterProgress" => $chapterProgress, "lectureProgress" => $lectureProgress, "purchase" => $purchase], 200);
        } else if ($request->has('userId') && $request->has('courseId') && $request->has('chapterId') && $request->has('lectureId')) {
            $lectureId = $request->input('lectureId');
            $courseId = $request->input('courseId');
            $chapterId = $request->input('chapterId');
            $lecture = Lecture::with(['muxData', 'attachments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
                ->where('id', $lectureId)
                ->where('courseId', $courseId)
                ->where('chapterId', $chapterId)
                ->first();
            return response()->json($lecture);
        }
        return response('Not Found', 404);
    }

    public function progress(Request $request)
    {
        if ($request->has('userId') && $request->has('courseId')) {
            $userId = $request->input('userId');
            $courseId = $request->input('courseId');
            $course = Course::with(['chapters' => function ($query) use ($userId) {
                $query
                    ->where('isPublished', true)
                    ->with(['lectures' => function ($query) use ($userId) {
                        $query
                            ->where('isPublished', true)
                            ->with(['userProgress' => function ($query) use ($userId) {
                                $query->where('userId', $userId);
                            }]);
                    }, 'userProgress' => function ($query) use ($userId) {
                        $query->where('userId', $userId);
                    }]);
            }])->find($courseId);
            return response()->json($course, 200);
        } else if ($request->has('userId') && $request->has('chapterIds')) {
            $validCompletedChapters = ChapterProgress::where('userId', $request->input('userId'))
                ->whereIn('chapterId', $request->input('chapterIds'))
                ->where('isCompleted', true)
                ->count();
            return response()->json($validCompletedChapters, 200);
        }
        return response("Not Found", 404);
    }

    public function purchase(Request $request)
    {
        if ($request->has('userId')) {
            $userId = $request->input('userId');
            $purchases = Purchase::whereHas('course', function ($query) use ($userId) {
                $query->where('userId', $userId);
            })->with('course')->get();
            return response()->json($purchases);
        }
        return response("Not Found", 404);
    }
}
