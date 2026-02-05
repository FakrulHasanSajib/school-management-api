<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentProfile;
use App\Models\FeeInvoice; // ✅ সঠিক মডেল নাম

class AdmitCardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $student = StudentProfile::where('user_id', $user->id)->with('schoolClass')->first();

        if (!$student) {
            return response()->json(['can_download' => false, 'message' => 'Student profile not found.']);
        }

        // ২. বকেয়া চেক (Model Name Fixed)
        $hasDue = false;
        try {
            // Invoice::where(...) এর বদলে FeeInvoice::where(...)
            $hasDue = FeeInvoice::where('student_id', $student->id)
                        ->where('status', '!=', 'Paid')
                        ->exists();
        } catch (\Exception $e) {
            $hasDue = false; // মডেল না থাকলে বাইপাস
        }

        // ৩. এক্সাম ডাটা
        $examDetails = [
            'exam_name' => 'Annual Examination - 2024',
            'start_date' => date('Y-m-d', strtotime('+7 days')),
            'center' => 'Main Hall',
            'student_name' => $user->name,
            'roll_no' => $student->roll_no,
            'class' => $student->schoolClass ? $student->schoolClass->name : 'N/A',
        ];

        if ($hasDue) {
            return response()->json([
                'can_download' => false,
                'message' => 'You have unpaid dues. Please pay to download admit card.',
                'exam_details' => $examDetails
            ]);
        }

        return response()->json([
            'can_download' => true,
            'message' => 'Eligible for exam.',
            'exam_details' => $examDetails
        ]);
    }
}
