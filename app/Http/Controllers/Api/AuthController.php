<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // ✅ Hash ইম্পোর্ট করা হয়েছে
use App\Models\User;

/**
 * @group Authentication
 *
 * APIs for managing user login, logout, password change and profile retrieval.
 */
class AuthController extends Controller
{
    /**
     * User Login
     *
     * Authenticates a user and returns an access token along with user details.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // টোকেন তৈরি
            $token = $user->createToken('auth_token')->plainTextToken;

            // রোল নির্ধারণ (Spatie অথবা ডাটাবেস কলাম থেকে)
            $role = $user->getRoleNames()->first() ?? $user->role;

            return response()->json([
                'status' => true,
                'message' => 'Login Successful',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role,
                    // ✅ এই ফিল্ডটি ফ্রন্টএন্ডে চেক করা হবে পপ-আপ দেখানোর জন্য
                    'must_change_password' => (bool) $user->must_change_password
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
     */
  public function profile(Request $request)
    {
        // 🛡️ সেফ মোড: সার্ভার ক্রাশ না করে এরর মেসেজ দেখাবে
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found in request'], 401);
            }

            // ১. স্টুডেন্ট ডাটা খোঁজার চেষ্টা
            // \App\Models\StudentProfile ক্লাসটি ঠিকমতো আছে কিনা চেক হবে
            $student = null;
            if (class_exists(\App\Models\StudentProfile::class)) {
                $student = \App\Models\StudentProfile::where('user_id', $user->id)->first();

                // যদি স্টুডেন্ট পাওয়া যায়, রিলেশনশিপ লোড করার চেষ্টা
                if ($student) {
                    // রিলেশনশিপগুলো আসলে আছে কিনা চেক করে লোড করা ভালো, তবে এখানে আমরা সরাসরি করছি
                    // যদি schoolClass বা section রিলেশন মডেলে না থাকে, এখানে এরর খেতে পারে
                    try {
                        $student->load(['schoolClass', 'section']);
                    } catch (\Exception $e) {
                        // রিলেশনশিপ না থাকলে ইগনোর করবে
                    }
                }
            }

            // ২. রেসপন্স তৈরি
            return response()->json([
                'status' => true,
                'message' => 'Profile fetched successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,

                    // এখানে স্টুডেন্ট ডাটা পাঠাচ্ছি
                    'student_profile' => $student,
                    'studentProfile' => $student, // সেইফটির জন্য দুই নামেই দিচ্ছি

                    // ডিবাগিং তথ্য (এটা দেখে বুঝব আসলে কী হচ্ছে)
                    'debug_info' => [
                        'user_id' => $user->id,
                        'student_found' => $student ? 'YES' : 'NO',
                        'table_check' => 'Query executed successfully'
                    ]
                ]
            ]);

        } catch (\Exception $error) {
            // 🛑 যদি কোনো কারণে কোড ফাটে, তাহলে এই ব্লকটি আসল এরর দেখাবে
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $error->getMessage(),
                'file' => $error->getFile(),
                'line' => $error->getLine()
            ], 500);
        }
    }

    /**
     * Change Password (Force Change)
     * * এটি স্টুডেন্ট ড্যাশবোর্ডের পপ-আপ থেকে কল হবে।
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:6|confirmed' // confirmed মানে new_password_confirmation ফিল্ড থাকতে হবে
        ]);

        $user = $request->user();

        // পাসওয়ার্ড আপডেট এবং ফ্ল্যাগ বন্ধ করা
        $user->update([
            'password' => Hash::make($request->new_password),
            'must_change_password' => false // ✅ পাসওয়ার্ড চেঞ্জ হয়ে গেলে আর পপ-আপ আসবে না
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully!'
        ]);
    }
}
