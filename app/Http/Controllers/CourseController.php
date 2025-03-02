<?php

namespace App\Http\Controllers;

use App\Models\ChapterAttachment;
use App\Models\Course;
use App\Models\MuxData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
                        ->orderBy('created_at', 'desc');
                }])->get();
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
            }, 'attachments', 'category'])->find($courseId);
            return response()->json($course, 200);
        }
        return response("Not Found", 404);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if ($user && ($user->role == "subadmin" || $user->role == "admin")) {
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
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                $course = Course::where('id', $courseId)->where('userId', $user->id)->first();
                if (!$course) {
                    return response("Unauthorized", 401);
                }
                $course->update($request->all());
                return response()->json($course, 200);
            } else {
                return response("Not Found", 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                $course = Course::where('id', $courseId)->first();
                if (!$course) {
                    return response("Not Found", 404);
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
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                if (!$courseOwner) {
                    return response("Unauthorized", 401);
                }
                $courseOwner->delete();
                return response()->json($courseOwner, 200);
            } else {
                return response('Not Found', 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                $courseOwner = Course::where('id', $courseId)->where('userId', $user->id)->first();
                if (!$courseOwner) {
                    return response("Unauthorized", 401);
                }
                $courseOwner->delete();
                return response()->json($courseOwner, 200);
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function publish($courseId)
    {
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
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
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                $course = Course::where('id', $courseId)->with(['chapters'])->first();
                if (!$course) {
                    return response('Not Found', 404);
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
        $user = Auth::user();
        if ($user && $user->role == "subadmin") {
            if ($courseId) {
                $course = Course::where('id', $courseId)->where('userId', $user->id)->with(['chapters'])->first();
                if (!$course) {
                    return response('Unauthorized', 401);
                }
                $course->update(['isPublished' => false]);
                return response()->json($course, 200);
            } else {
                return response('Not Found', 404);
            }
        } else if ($user && $user->role == "admin") {
            if ($courseId) {
                $course = Course::where('id', $courseId)->with(['chapters'])->first();
                if (!$course) {
                    return response('Not Found', 404);
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
