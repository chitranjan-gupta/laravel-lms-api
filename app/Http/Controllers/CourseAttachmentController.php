<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CourseAttachmentController extends Controller
{
    public function create(Request $request, $courseId)
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
                    $attachment = CourseAttachment::create([
                        'url' => $url,
                        'name' => basename($url),
                        'courseId' => $courseId
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

    public function delete($courseId, $attachmentId)
    {
        if (Auth::user()) {
            if ($courseId) {
                if ($attachmentId) {
                    $user = Auth::user();
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $attachment = CourseAttachment::where('courseId', $courseId)->where('id', $attachmentId)->delete();
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
