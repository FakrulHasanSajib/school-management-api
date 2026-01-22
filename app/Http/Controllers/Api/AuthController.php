<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * @group Authentication
 *
 * APIs for managing user login, logout, and profile retrieval.
 */
class AuthController extends Controller
{
    /**
     * User Login
     *
     * Authenticates a user and returns an access token along with user details.
     *
     * @bodyParam email string required The email of the user. Example: admin@school.com
     * @bodyParam password string required The password of the user. Example: password
     *
     * @response 200 {
     * "status": true,
     * "message": "Login Successful",
     * "token": "1|laravel_sanctum_token_string...",
     * "user": {
     * "id": 1,
     * "name": "Admin User",
     * "role": "super-admin"
     * }
     * }
     * @response 401 {
     * "status": false,
     * "message": "Email or Password does not match."
     * }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            
            // টোকেন তৈরি (Role অনুযায়ী টোকেনের নাম দিচ্ছি)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login Successful',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->getRoleNames()->first(), // Spatie Role
                ]
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Email or Password does not match.',
        ], 401);
    }

    /**
     * User Logout
     *
     * Revokes the current user's access token.
     *
     * @authenticated
     *
     * @response 200 {
     * "status": true,
     * "message": "Logged out successfully"
     * }
     */
    public function logout(Request $request)
    {
        // বর্তমান টোকেনটি ডিলিট করে দিচ্ছি
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get User Profile
     *
     * Retrieves the currently authenticated user's information.
     *
     * @authenticated
     *
     * @response 200 {
     * "status": true,
     * "data": {
     * "id": 1,
     * "name": "Admin User",
     * "email": "admin@school.com",
     * "email_verified_at": "2024-01-01T00:00:00.000000Z",
     * "created_at": "2024-01-01T00:00:00.000000Z",
     * "updated_at": "2024-01-01T00:00:00.000000Z"
     * }
     * }
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user()
        ]);
    }
}