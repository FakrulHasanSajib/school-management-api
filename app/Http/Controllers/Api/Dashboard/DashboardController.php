<?php

namespace App\Http\Controllers\Api\Dashboard; // ✅ নেমস্পেস আলাদা করা হলো

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\Payment; // ইনকাম দেখার জন্য
use App\Models\Attendance; // বোনাস: আজকের উপস্থিতি

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        // ১. মোট ছাত্র
        $totalStudents = StudentProfile::count();

        // ২. মোট শিক্ষক (TeacherProfile তৈরি না থাকলে User দিয়ে চেক করতে পারেন)
        // আমরা ধরে নিচ্ছি TeacherProfile মডেল আছে
        $totalTeachers = TeacherProfile::count();

        // ৩. মোট ইনকাম (Payment টেবিল থেকে)
        $totalIncome = Payment::sum('amount');

        // ৪. আজকের উপস্থিতি (অপশনাল, চাইলে রাখতে পারেন)
        $todaysPresent = Attendance::whereDate('date', now()->toDateString())
                                   ->where('status', 'Present')
                                   ->count();

        return response()->json([
            'status' => true,
            'data' => [
                'total_students' => $totalStudents,
                'total_teachers' => $totalTeachers,
                'total_income'   => $totalIncome,
                'todays_present' => $todaysPresent
            ]
        ], 200);
    }
}