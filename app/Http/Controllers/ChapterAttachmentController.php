<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterAttachment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChapterAttachmentController extends Controller
{
    public function create(Request $request, $courseId, $chapterId)
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
                    $attachment = ChapterAttachment::create([
                        'url' => $url,
                        'name' => basename($url),
                        'chapterId' => $chapterOwner->id
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
                    $attachment = ChapterAttachment::create([
                        'url' => $url,
                        'name' => basename($url),
                        'chapterId' => $chapter->id
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

    public function delete($courseId, $chapterId, $attachmentId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($attachmentId) {
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $chapterOwner = Course::where('id', $chapterId)->where('courseId', $courseOwner->id)->first();
                    if (!$chapterOwner) {
                        return response("Unauthorized", 401);
                    }
                    $attachment = ChapterAttachment::where('chapterId', $chapterOwner->id)->where('id', $attachmentId)->delete();
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
                    $chapter = Course::where('id', $chapterId)->where('courseId', $course->id)->first();
                    if (!$chapter) {
                        return response("Not Found", 404);
                    }
                    $attachment = ChapterAttachment::where('chapterId', $chapter->id)->where('id', $attachmentId)->delete();
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
