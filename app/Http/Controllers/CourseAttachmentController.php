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
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($request->has('url')) {
                    $url = $request->input('url');
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $attachment = CourseAttachment::create([
                        'url' => $url,
                        'name' => basename($url),
                        'courseId' => $courseOwner->id
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
                    $attachment = CourseAttachment::create([
                        'url' => $url,
                        'name' => basename($url),
                        'courseId' => $course->id
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
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                if ($attachmentId) {
                    $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                    if (!$courseOwner) {
                        return response("Unauthorized", 401);
                    }
                    $attachment = CourseAttachment::where('courseId', $courseOwner->id)->where('id', $attachmentId)->delete();
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
                    $attachment = CourseAttachment::where('courseId', $course->id)->where('id', $attachmentId)->delete();
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
