<?php

namespace App\Http\Controllers;

use App\Models\ChapterProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChapterProgressController extends Controller
{
    public function index(Request $request, $courseId, $chapterId)
    {
        if (Auth::user()) {
            if ($courseId) {
                if ($chapterId) {
                    if ($request->has('isCompleted')) {
                        $user = Auth::user();
                        $userProgress = ChapterProgress::updateOrCreate(['userId' => $user->id, 'chapterId' => $chapterId], ['isCompleted' => $request->input('isCompleted')]);
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
            return response('Unauthorized', 401);
        }
    }
}
