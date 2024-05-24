<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->role == "admin") {
            $applications = Application::all();

            return response()->json($applications, 200);
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function apply()
    {
        $user = Auth::user();
        if ($user && $user->role == "user") {
            $existingApplication = Application::where('userId', $user->id)->first();
            if ($existingApplication) {
                return response('Application already present', 400);
            }
            $application = Application::create([
                'userId' => $user->id,
                'name' => $user->name,
                'status' => 'applied'
            ]);
            return response()->json($application, 200);
        } else {
            return response('Unauthorized', 401);
        }
    }
}
