<?php

namespace App\Http\Controllers;

use App\Models\ChapterAttachment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChapterAttachmentController extends Controller
{
    public function create(Request $request, $courseId, $chapterId)
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
                    $attachment = ChapterAttachment::create([
                        'url' => $url,
                        'name' => basename($url),
                        'chapterId' => $chapterId
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
        if (Auth::user()) {
            if ($courseId) {
                if ($attachmentId) {
                    $user = Auth::user();
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $attachment = ChapterAttachment::where('chapterId', $chapterId)->where('id', $attachmentId)->delete();
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
