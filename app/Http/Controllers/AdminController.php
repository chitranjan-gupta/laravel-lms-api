<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lecture;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function courses()
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            $courses = Course::with(['category'])->get();
            return response()->json($courses, 200);
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function categories()
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            $categories = Category::all();
            return response()->json($categories, 200);
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function addCategory(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            if ($request->has('name')) {
                $name = $request->input('name');
                $existingCategory = Category::where('name', $name)->first();
                if ($existingCategory) {
                    return response('Category Already Exist', 400);
                }
                $category = Category::create(['name' => $name]);
                return response()->json($category, 200);
            } else {
                return response('Missing name field', 400);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function editCategory(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            if ($request->has('name') && $request->has('categoryId')) {
                $name = $request->input('name');
                $categoryId = $request->input('categoryId');
                Log::info($categoryId);
                $existingCategory = Category::where('id', $categoryId)->first();
                if (!$existingCategory) {
                    return response("Category Doesn't Exist", 400);
                }
                $existingCategory->update(['name' => $name]);
                return response()->json($existingCategory, 200);
            } else {
                return response('Missing name or categoryId field', 400);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function deleteCategory(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            if ($request->has('categoryId')) {
                $categoryId = $request->input('categoryId');
                Log::info($categoryId);
                $existingCategory = Category::where('id', $categoryId)->first();
                if (!$existingCategory) {
                    return response("Category Doesn't Exist", 400);
                }
                $existingCategory->delete();
                return response()->json($existingCategory, 200);
            } else {
                return response('Missing categoryId field', 400);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function users()
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            $users = User::where('role', 'user')->get();
            return response()->json($users, 200);
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function subadmins()
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            $subadmins = User::where('role', 'subadmin')->get();
            return response()->json($subadmins, 200);
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function chapter(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            if ($request->has("userId") && $request->has("courseId") && $request->has("chapterId")) {
                $courseId = $request->input('courseId');
                $chapterId = $request->input('chapterId');
                $chapter = Chapter::where("id", $chapterId)->where("courseId", $courseId)
                    ->with(['lectures' => function ($query) {
                        $query->orderBy('position', 'asc');
                    }, 'attachments' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    }])->first();
                return response()->json($chapter, 200);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function lecture(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            if ($request->has('userId') && $request->has('courseId') && $request->has('chapterId') && $request->has('lectureId')) {
                $lectureId = $request->input('lectureId');
                $courseId = $request->input('courseId');
                $chapterId = $request->input('chapterId');
                $lecture = Lecture::with(['muxData', 'attachments' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }])
                    ->where('id', $lectureId)
                    ->where('courseId', $courseId)
                    ->where('chapterId', $chapterId)
                    ->first();
                return response()->json($lecture);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    private function groupByCourse($purchases)
    {
        $grouped = [];

        foreach ($purchases as $purchase) {
            $courseTitle = $purchase->course->title;
            if (!isset($grouped[$courseTitle])) {
                $grouped[$courseTitle] = 0;
            }
            $grouped[$courseTitle] += $purchase->course->price;
        }
        return $grouped;
    }

    public function analytics()
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            $purchases = Purchase::with(['course'])->get();
            $groupedEarning = $this->groupByCourse($purchases);

            $data = collect($groupedEarning)->map(function ($total, $courseTitle) {
                return ['name' => $courseTitle, 'total' => $total];
            })->values();

            $totalRevenue = $data->sum('total');
            $totalSales = $purchases->count();
            return response()->json(['data' => $data, 'totalRevenue' => $totalRevenue, 'totalSales' => $totalSales], 200);
        } else {
            return response('Unauthorized', 401);
        }
    }
}
