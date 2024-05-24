<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function courses()
    {
        $courses = Course::with([
            'category',
            'chapters' => function ($query) {
                $query
                    ->where('isPublished', true)
                    ->with(['lectures' => function ($query) {
                        $query
                            ->where('isPublished', true);
                    }]);
            }
        ])
            ->where('isPublished', true)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($courses, 200);
    }

    public function search(Request $request)
    {
        if ($request->has('title') || $request->has('categoryId')) {
            $courses = Course::where('isPublished', true)
                ->when($request->has('title'), function ($query) use ($request) {
                    $query->where('title', 'like', '%' . $request->input('title') . '%');
                })
                ->when($request->has('categoryId'), function ($query) use ($request) {
                    $query->where('categoryId', $request->input('categoryId'));
                })
                ->with(['category', 'chapters' => function ($query) {
                    $query
                        ->where('isPublished', true)
                        ->with(['lectures' => function ($query) {
                            $query
                                ->where('isPublished', true);
                        }])
                        ->orderBy('created_at', 'desc')
                        ->get();
                }]);
            return response()->json($courses, 200);
        } else {
            $courses = Course::where('isPublished', true)
                ->with(['category', 'chapters' => function ($query) {
                    $query
                        ->where('isPublished', true)
                        ->with(['lectures' => function ($query) {
                            $query
                                ->where('isPublished', true);
                        }]);
                }])
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($courses, 200);
        }
    }

    public function get($courseId)
    {
        if ($courseId) {
            $course = Course::with(['chapters' => function ($query) {
                $query->where('isPublished', true)
                    ->orderBy('position', 'asc')
                    ->with(['lectures' => function ($query) {
                        $query->where('isPublished', true)
                            ->orderBy('position', 'asc');
                    }]);
            }])->find($courseId);
            return response()->json($course, 200);
        }
        return response("Not Found", 404);
    }

    public function create(Request $request)
    {
        if (Auth::user()) {
            $user = Auth::user();
            if ($request->has('title')) {
                $title = $request->input('title');
                $course = Course::create([
                    'userId' => $user->id,
                    'title' => $title,
                ]);
                return response()->json($course, 200);
            } else {
                return response("Title is missing", 400);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function set(Request $request, $courseId)
    {
        if (Auth::user()) {
            if ($courseId) {
                $user = Auth::user();
                $course = Course::where('id', $courseId)->where('userId', $user->id)->first();
                if (!$course) {
                    return response("Unauthorized", 401);
                }
                $course->update($request->all());
                return response()->json($course, 200);
            } else {
                return response("Not Found", 404);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function delete($courseId)
    {
        if ($courseId) {
            if (auth()->user()) {
                $userId = auth()->user()->id;
                $course = Course::where('id', $courseId)->where('userId', $userId)->with(['chapters.lectures.muxData']);
                if (!$course) {
                    return response("Unauthorized", 401);
                }
                
            } else {
                return response("Unauthorized", 401);
            }
        }
        return response("Not Found", 404);
    }

    public function publish($courseId)
    {
        if (Auth::user()) {
            if ($courseId) {
                $user = Auth::user();
                $course = Course::where('id', $courseId)->where('userId', $user->id)->with(['chapters'])->first();
                if (!$course) {
                    return response('Unauthorized', 401);
                }
                if (!$course->title || !$course->description || !$course->imageUrl || !$course->categoryId || !$course->chapters->contains('isPublished', true)) {
                    return response('Missing required fields', 400);
                }
                $course->update(['isPublished' => true]);
                return response()->json($course, 200);
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function unpublish($courseId)
    {
        if (Auth::user()) {
            if ($courseId) {
                $user = Auth::user();
                $course = Course::where('id', $courseId)->where('userId', $user->id)->with(['chapters'])->first();
                if (!$course) {
                    return response('Unauthorized', 401);
                }
                $course->update(['isPublished' => false]);
                return response()->json($course, 200);
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }
}
