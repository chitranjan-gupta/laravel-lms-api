<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function purchases(Request $request)
    {
        $user = Auth::user();
        if ($user->id && $request->has('courseId')) {
            $courseId = $request->input("courseId");
            $purchase = Purchase::where('userId', $user->id)->where('courseId', $courseId)->first();
            return response()->json($purchase, 200);
        } else if ($user->id) {
            $purchasedCourse = Purchase::where('userId', $user->id)
                ->with(['course' => function ($query) {
                    $query
                        ->with('category')
                        ->with(['chapters' => function ($query) {
                            $query->where('isPublished', true);
                        }]);
                }])->get();
            return response()->json($purchasedCourse, 200);
        } else {
            return response('Not Found', 404);
        }
    }
}
