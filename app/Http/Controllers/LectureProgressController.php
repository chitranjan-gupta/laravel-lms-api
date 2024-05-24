<?php

namespace App\Http\Controllers;

use App\Models\LectureProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LectureProgressController extends Controller
{
    public function index(Request $request, $courseId, $chapterId, $lectureId)
    {
        if (Auth::user()) {
            if ($courseId) {
                if ($chapterId) {
                    if ($lectureId) {
                        if ($request->has('isCompleted')) {
                            $user = Auth::user();
                            $userProgress = LectureProgress::updateOrCreate(['userId' => $user->id, 'lectureId' => $lectureId], ['isCompleted' => $request->input('isCompleted')]);
                            return response()->json($userProgress);
                        } else {
                            return response('Missing fields', 400);
                        }
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
}
