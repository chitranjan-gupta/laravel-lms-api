<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function purchases(Request $request)
    {
        if ($request->has("userId") && $request->has('courseId')) {
            $userId = $request->input("userId");
            $courseId = $request->input("courseId");
            $purchase = Purchase::where('userId', $userId)->where('courseId', $courseId)->first();
            return response()->json($purchase, 200);
        } else if ($request->has('userId')) {
            $userId = $request->input("userId");
            $purchasedCourse = Purchase::where('userId', $userId)
                ->with(['course' => function ($query) {
                    $query
                        ->with('category')
                        ->with(['chapters' => function ($query) {
                            $query->where('isPublished', true);
                        }]);
                }])->get();
            return response()->json($purchasedCourse, 200);
        }
        return response('Not Found', 404);
    }
}
