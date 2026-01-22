<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // Login API
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            
            // টোকেন তৈরি (Role অনুযায়ী টোকেনের নাম দিচ্ছি)
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

    // Logout API
    public function logout(Request $request)
    {
        // বর্তমান টোকেনটি ডিলিট করে দিচ্ছি
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    // User Profile API (Check current user)
    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user()
        ]);
    }
}