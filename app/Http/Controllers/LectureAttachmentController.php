<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lecture;
use App\Models\LectureAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LectureAttachmentController extends Controller
{
    public function create(Request $request, $courseId, $chapterId, $lectureId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($request->has('url')) {
                    $url = $request->input('url');
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $chapterOwner = Chapter::where('id', $chapterId)->where('courseId', $courseOwner->id)->first();
                    if (!$chapterOwner) {
                        return response("Unauthorized", 401);
                    }
                    $lectureOwner = Lecture::where('id', $lectureId)->where('chapterId', $chapterOwner->id)->where('courseId', $courseOwner->id)->first();
                    if (!$lectureOwner) {
                        return response("Unauthorized", 401);
                    }
                    $attachment = LectureAttachment::create([
                        'url' => $url,
                        'name' => basename($url),
                        'lectureId' => $lectureOwner->id
                    ]);
                    return response()->json($attachment, 200);
                }
            } else {
                return response("Not Found", 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                if ($request->has('url')) {
                    $url = $request->input('url');
                    $course = Course::where('id', $courseId)->first();
                    if (!$course) {
                        return response("Not Found", 404);
                    }
                    $chapter = Chapter::where('id', $chapterId)->where('courseId', $course->id)->first();
                    if (!$chapter) {
                        return response("Not Found", 404);
                    }
                    $lecture = Lecture::where('id', $lectureId)->where('chapterId', $chapter->id)->where('courseId', $course->id)->first();
                    if (!$lecture) {
                        return response("Not Found", 404);
                    }
                    $attachment = LectureAttachment::create([
                        'url' => $url,
                        'name' => basename($url),
                        'lectureId' => $lecture->id
                    ]);
                    return response()->json($attachment, 200);
                }
            } else {
                return response("Not Found", 404);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function delete($courseId, $chapterId, $lectureId, $attachmentId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($attachmentId) {
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $chapterOwner = Chapter::where('id', $chapterId)->where('courseId', $courseOwner->id)->first();
                    if (!$chapterOwner) {
                        return response("Unauthorized", 401);
                    }
                    $lectureOwner = Lecture::where('id', $lectureId)->where('chapterId', $chapterOwner->id)->where('courseId', $courseOwner->id)->first();
                    if (!$lectureOwner) {
                        return response("Unauthorized", 401);
                    }
                    $attachment = LectureAttachment::where('lectureId', $lectureOwner->id)->where('id', $attachmentId)->delete();
                    return response()->json($attachment);
                } else {
                    return response("Not Found", 404);
                }
            } else {
                return response("Not Found", 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                if ($attachmentId) {
                    $course = Course::where('id', $courseId)->first();
                    if (!$course) {
                        return response("Not Found", 404);
                    }
                    $chapter = Chapter::where('id', $chapterId)->where('courseId', $course->id)->first();
                    if (!$chapter) {
                        return response("Not Found", 404);
                    }
                    $lecture = Lecture::where('id', $lectureId)->where('chapterId', $chapter->id)->where('courseId', $course->id)->first();
                    if (!$lecture) {
                        return response("Not Found", 404);
                    }
                    $attachment = LectureAttachment::where('lectureId', $lecture->id)->where('id', $attachmentId)->delete();
                    return response()->json($attachment);
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
