<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\Payment;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        try {
            // ১. কার্ডের ডাটা
            $stats = [
                'total_students' => StudentProfile::count(),
                'total_teachers' => TeacherProfile::count(),
                'total_income'   => Payment::sum('amount'),
                'todays_present' => Attendance::whereDate('date', now()->toDateString())
                                    ->where('status', 'Present')->count()
            ];

            // ২. রিসেন্ট পেমেন্ট (লেটেস্ট ৫টি) - স্টুডেন্ট নাম সহ
            $recent_payments = Payment::with(['invoice.student.user', 'invoice.feeType'])
                                ->orderBy('id', 'desc')
                                ->take(5)
                                ->get()
                                ->map(function($payment) {
                                    return [
                                        'id' => $payment->id,
                                        'student_name' => $payment->invoice->student->user->name ?? 'Unknown',
                                        'fee_type' => $payment->invoice->feeType->name ?? 'Fee',
                                        'amount' => $payment->amount,
                                        'date' => $payment->created_at->format('d M, Y'),
                                        'status' => 'Paid'
                                    ];
                                });

            // ৩. চার্ট ডাটা: মাসিক ইনকাম (চলতি বছরের) - Dynamic Income Chart
            $incomeData = Payment::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

            // সব মাসের ডাটা রেডি করা (যাতে খালি মাসে ০ দেখায়)
            $monthly_income = [];
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            foreach (range(1, 12) as $m) {
                $monthly_income[] = $incomeData[$m] ?? 0;
            }

            // ৪. চার্ট ডাটা: আজকের উপস্থিতির অনুপাত - Dynamic Attendance Chart
            $attendanceCounts = Attendance::whereDate('date', now()->toDateString())
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            // যদি আজ হাজিরা না নেওয়া হয়, তবে সব ০ থাকবে
            $attendance_ratio = [
                $attendanceCounts['Present'] ?? 0,
                $attendanceCounts['Absent'] ?? 0,
                $attendanceCounts['Late'] ?? 0
            ];

            return response()->json([
                'status' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_payments' => $recent_payments,
                    'chart_data' => [
                        'months' => $months,
                        'income' => $monthly_income,
                        'attendance' => $attendance_ratio
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
