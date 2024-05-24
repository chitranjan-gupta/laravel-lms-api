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
        if (Auth::user()) {
            if ($courseId) {
                if ($request->has('url')) {
                    $user = Auth::user();
                    $url = $request->input('url');
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $chapterOwner = Chapter::where('id', $chapterId)->where('courseId', $courseId)->first();
                    if (!$chapterOwner) {
                        return response("Unauthorized", 401);
                    }
                    $lectureOwner = Lecture::where('id', $lectureId)->where('chapterId', $chapterId)->where('courseId', $courseId)->first();
                    if (!$lectureOwner) {
                        return response("Unauthorized", 401);
                    }
                    $attachment = LectureAttachment::create([
                        'url' => $url,
                        'name' => basename($url),
                        'lectureId' => $lectureId
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
        if (Auth::user()) {
            if ($courseId) {
                if ($attachmentId) {
                    $user = Auth::user();
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $chapterOwner = Chapter::where('id', $chapterId)->where('courseId', $courseId)->first();
                    if (!$chapterOwner) {
                        return response("Unauthorized", 401);
                    }
                    $lectureOwner = Lecture::where('id', $lectureId)->where('chapterId', $chapterId)->where('courseId', $courseId)->first();
                    if (!$lectureOwner) {
                        return response("Unauthorized", 401);
                    }
                    $attachment = LectureAttachment::where('lectureId', $lectureId)->where('id', $attachmentId)->delete();
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
