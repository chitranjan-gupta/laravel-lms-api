<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function courses(){
        $user = Auth::user();
        if($user && $user->role == "admin"){
            $courses = Course::with(['category'])->get();
            return response()->json($courses, 200);
        }else{
            return response('Unauthroized', 401);
        }
    }

    public function categories(){
        $user = Auth::user();
        if($user && $user->role == "admin"){
            $categories = Category::all();
            return response()->json($categories, 200);
        }else{
            return response('Unauthroized', 401);
        }
    }

    public function users(){
        $user = Auth::user();
        if($user && $user->role == "admin"){
            $users = User::where('role', 'user')->get();
            return response()->json($users, 200);
        }else{
            return response('Unauthroized', 401);
        }
    }
    
    public function subadmins(){
        $user = Auth::user();
        if($user && $user->role == "admin"){
            $subadmins = User::where('role', 'subadmin')->get();
            return response()->json($subadmins, 200);
        }else{
            return response('Unauthroized', 401);
        }
    }
}
