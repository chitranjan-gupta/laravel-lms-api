<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GoogleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function respondWithToken(string $token, int $ttl)
    {
        return [
            'status' => 200,
            'success' => true,
            'message' => 'You have logged in successfully !',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expiry_in' => $ttl * 60
        ];
    }

    public function register(Request $request)
    {
        // Validate the input data
        $validated = $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&]/'
        ]);

        try {
            // Create the user with validated data
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password'])  // Hash the password
            ]);

            // Respond with user data and status 200
            return response()->json([
                'userId' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role
            ], 200);
        } catch (\Exception $e) {
            // Handle any exception (e.g., database errors)
            return response()->json([
                'error' => 'Error saving user: ' . $e->getMessage()
            ], 500);  // HTTP 500 for internal server error
        }
    }

    public function login(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Attempt to log in the user with the provided credentials
        if ($token = auth('api')->attempt($request->only('email', 'password'))) {
            // Get the user associated with the token
            $user = auth('api')->user();
            // Log::info(env('FRONTEND_APP_URL'));

            $ttl = auth('api')->factory()->getTTL(); // Returns the TTL in minutes, e.g., 60

            // Generate a response with the token
            $response = $this->respondWithToken($token, $ttl);

            // Create a secure cookie for the token
            $cookie = cookie('access_token', $token, $ttl, '/', null, true, true, false, 'None');

            // Return a response with user info and the token in a cookie
            return response()->json([
                ...$response,
                'userId' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role
            ], 200)->cookie($cookie);
        }

        // Return an error response if login failed
        return response()->json([
            'message' => 'Invalid Email or Password'
        ], 401);
    }

    public function refresh()
    {
        try {
            $token = auth('api')->refresh();
            $user = auth('api')->user();
            $ttl = auth('api')->factory()->getTTL(); // Returns the TTL in minutes, e.g., 60
            $response = $this->respondWithToken($token, $ttl);
            $cookie = cookie('access_token', $token, $ttl, '/', null, true, true, false, 'None');
            return response()->json([...$response, "userId" => $user->id, "email" => $user->email, "name" => $user->name, "role" => $user->role], 200)->cookie($cookie);
        } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
            return response("Unauthorized", 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response("Token Not Present or Invalid", 401);
        }
    }

    public function logout()
    {
        if (auth('api')->user()) {
            auth('api')->logout(true);
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

    public function changePassword(Request $request){
        $user = auth('api')->user();
        if($user){
            $validator = Validator::make($request->all(), [
                'currentPassword' => 'required|string',
                'newPassword' => 'required|string|min:8|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&]/'
            ]);
            $errors = $validator->errors();
             if ($validator->fails()) {
                return response()->json(["currentPassword" => $errors->get('currentPassword'), "newPassword" => $errors->get('newPassword')], 400);
             }
            $validated = $validator->validated();
            $existingUser = User::where('id', $user->id)->first();
            if($existingUser){
                if(Hash::check($validated['currentPassword'], $existingUser->password)){
                    $existingUser->password = Hash::make($validated['newPassword']);
                    $existingUser->save();
                    return response()->json([
                        "message" => "Password updated successfully"
                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Password does not match"
                    ], 400);
                }
            } else {
                return response("User not found", 404);
            }
        } else {
            return response("Unauthorized", 401);
        }
    }

    public function oauth_register(){
        $url = Socialite::driver('google')->with(['access_type' => 'offline', 'prompt' => 'consent'])->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url], 200);
    }

    public function oauth_login(){
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url], 200);
    }

    public function generateUniqueUsername(string $name)
    {
        // Generate a base username by making the name lowercase and replacing spaces with underscores
        $baseUsername = Str::slug($name, '_');

        // Add a salt value to the base username to ensure uniqueness
        $salt = Str::random(8); // 8-character random string

        // Combine the base username with the salt value
        $username = $baseUsername . '_' . $salt;

        // Check if the generated username already exists in the database
        while (User::where('username', $username)->exists()) {
            // If the username already exists, generate a new salt and try again
            $salt = Str::random(8);
            $username = $baseUsername . '_' . $salt;
        }

        return $username;
    }

    public function oauth_success(){
        $googleUser = Socialite::driver('google')->stateless()->user();
        Log::info(json_encode($googleUser));
        // Extract the information
        $googleId = $googleUser->getId();
        $accessToken = $googleUser->token;
        $refreshToken = $googleUser->refreshToken; // Access the refresh token
        $expiresIn = $googleUser->expiresIn; // Get the expires_in field from the raw data
        // If expires_in is present, calculate the expiration time
        $expiresAt = $expiresIn ? now()->addSeconds($expiresIn) : null;
        $avatarUrl = $googleUser->getAvatar();
        $name = $googleUser->getName();
        $email = $googleUser->getEmail();
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'username' => $this->generateUniqueUsername($name),
                'name' => $name,
                'password' => Hash::make(Str::random(16)) // Create a random password
            ]
        );
        // Now save the Google OAuth tokens in the google_oauth table
        if($refreshToken){
            GoogleDetail::updateOrCreate(
                ['google_id' => $googleId], // Google ID must be unique
                [
                    'avatar' => $avatarUrl,
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer', // OAuth token type (usually Bearer)
                    'expires_at' => $expiresAt,
                    'userId' => $user->id, // Link the OAuth token to your user
                ]
            );
        } else {
            GoogleDetail::updateOrCreate(
                ['google_id' => $googleId], // Google ID must be unique
                [
                    'access_token' => $accessToken,
                    'expires_at' => $expiresAt,
                    'userId' => $user->id, // Link the OAuth token to your user
                ]
            );
        }
        $token = auth('api')->login($user);
        if($token){
            $ttl = auth()->factory()->getTTL();
            $response = $this->respondWithToken($token, $ttl);
            $cookie = cookie('access_token', $token, $ttl, "/", null, true, true, false, 'None');
            return response()->json([...$response, "userId" => $user->id, "email" => $user->email, "name" => $user->name, "role" => $user->role], 200)->cookie($cookie);
        }else{
            return response("Unauthorized", 401);
        }
    }

    public function oauth_fail(){
        return response('OAUTH failed', 401);
    }

    public function oauth_me(){
        return response('todo:me', 200);
    }

    public function oauth_update(){
        return response('todo:update', 200);
    }
}
