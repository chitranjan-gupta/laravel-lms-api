<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function all(){
        $user = Auth::user();
        if($user && $user->role == "admin"){
            $notifications = Notification::with(['user'])->get();
            return response()->json($notifications, 200);
        }else{
            return response("Unauthorized", 401);
        }
    }

    public function read(){
        $user = Auth::user();
        if($user){
            $notification = Notification::where('userId', $user->id)->first();
            if(!$notification){
                return response("Not Found", 404);
            }
            return response()->json($notification, 200);
        }else{
            return response("Unauthorized", 401);
        }
    }

    public function add(Request $request){
        $user = Auth::user();
        if($user){
            if($request->has("expopushtoken")){
                $expopushtoken = $request->input("expopushtoken");
                $notification = Notification::create(['expopushtoken' => $expopushtoken]);
                return response()->json($notification, 200);
            }else{
                return response("expopushtoken is missing", 400);
            }
        }else{
            return response("Unauthorized", 401);
        }
    }

    public function delete(Request $request){
        $user = Auth::user();
        if($user && $user->role == "admin"){
            if($request->has('removeNotificationId')){
                $removeNotificationId = $request->input('removeNotificationId');
                $notification = Notification::with(['user'])->where('id', $removeNotificationId)->first();
                if(!$notification){
                    return response("Notification not found", 404);
                }
                return response()->json($notification, 200);
            }else{
                return response("removeNotificationId is missing", 400);
            }
        }else if($user){
            if($request->has('removeNotificationId')){
                $removeNotificationId = $request->input('removeNotificationId');
                $notification = Notification::with(['user'])->where('id', $removeNotificationId)->first();
                if(!$notification){
                    return response("Unauthorized", 401);
                }
                return response()->json($notification, 200);
            }else{
                return response("removeNotificationId is missing", 400);
            }
        }else{
            return response("Unauthorized", 401);
        }
    }
}
