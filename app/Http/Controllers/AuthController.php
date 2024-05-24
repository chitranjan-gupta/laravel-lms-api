<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function userRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);
        $data = $request->all();

        if ($data) {
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->username = $data['username'];
            $user->password = Hash::make($data['password']);
            $result = $user->save();
            if ($result) {
                return response()->json(['userId' => $user->id, 'email' => $user->email, 'username' => $user->username, "role" => $user->role], 200);
            } else {
                return response("Error in Saving user", 500);
            }
        } else {
            return response()->json([
                'error' => 'Validator error'
            ], 401);
        }
    }

    public function userLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $data = $request->all();

        if (!$data) {
            return response()->json([
                'error' => 'Validator error'
            ], 401);
        } else {
            $loginEmail = array(
                'email' => $data['email'],
                'password' => $data['password']
            );
            $loggedin = false;
            $token = '';
            $user = null;
            if ($log2 = Auth::attempt($loginEmail)) {
                $loggedin = true;
                $token = $log2;
                global $user;
                $user = User::where('email', $data['email'])->first();
            }
            if ($loggedin == false) {
                return response()->json([
                    'message' => 'Invalid Email/Password'
                ], 401);
            }
        }

        // Log::info(env('FRONTEND_APP_URL'));

        $response = $this->respondWithToken($token);
        $cookie = cookie('access_token', $token, auth()->factory()->getTTL() * 60 * 60, "/", null, true, true, false, 'None');
        return response()->json([...$response, "userId" => $user->id, "email" => $user->email, "name" => $user->name, "role" => $user->role], 200)->cookie($cookie);
    }

    public function respondWithToken($token)
    {
        return [
            'status' => 200,
            'success' => true,
            'message' => 'You have logined successfully !',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expiry_in' => auth()->factory()->getTTL() * 60
        ];
    }

    public function refresh()
    {
        if (Auth::user()) {
            $user = Auth::user();
            $token = auth()->refresh(true);
            $response = $this->respondWithToken($token);
            $cookie = cookie('access_token', $token, auth()->factory()->getTTL() * 60);
            return response()->json([...$response, "userId" => $user->id, "email" => $user->email, "name" => $user->name, "role" => $user->role], 200)->cookie($cookie);
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function logout()
    {
        if (auth()->user()) {
            auth()->logout(true);
            $cookie = cookie('access_token', null);
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'logged out'
            ], 200)->cookie($cookie);
        } else {
            return response("Unauthorized", 401);
        }
    }
}
