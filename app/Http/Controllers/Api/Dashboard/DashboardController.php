<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\StudentProfile;
use App\Models\TeacherProfile; // অথবা User যেখানে role='teacher'
use App\Models\Payment;
use App\Models\Attendance;
use App\Models\Expense; // ✅ নতুন যোগ করা হয়েছে
use App\Models\Payroll; // ✅ নতুন যোগ করা হয়েছে
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        try {
            // ১. ইনকাম ক্যালকুলেশন
            $total_income = Payment::sum('amount');

            // ২. এক্সপেন্স ক্যালকুলেশন (খরচ + স্যালারি)
            $total_general_expense = Expense::sum('amount');
            $total_salary_expense = Payroll::sum('net_salary');
            $total_expense = $total_general_expense + $total_salary_expense;

            // ৩. নেট ব্যালেন্স (লাভ/উদ্বৃত্ত)
            $net_balance = $total_income - $total_expense;

            // ৪. কার্ডের ডাটা
            $stats = [
                'total_students' => StudentProfile::count(),
                'total_teachers' => TeacherProfile::count(),
                'total_income'   => $total_income,
                'total_expense'  => $total_expense, // ✅ ড্যাশবোর্ডে পাঠানোর জন্য
                'net_balance'    => $net_balance,   // ✅ ড্যাশবোর্ডে পাঠানোর জন্য
                'todays_present' => Attendance::whereDate('date', now()->toDateString())
                                    ->where('status', 'Present')->count()
            ];

            // ৫. রিসেন্ট পেমেন্ট (Student নামসহ)
            $recent_payments = Payment::with(['invoice.student.user', 'invoice.feeType'])
                                ->orderBy('id', 'desc')
                                ->take(5)
                                ->get()
                                ->map(function($payment) {
                                    return [
                                        'id' => $payment->id,
                                        // যদি ইউজার ডিলিট হয়ে যায়, তবুও যেন এরর না দেয়
                                        'student_name' => $payment->invoice->student->user->name ?? 'Unknown Student',
                                        'fee_type' => $payment->invoice->feeType->name ?? 'Fee',
                                        'amount' => $payment->amount,
                                        'date' => $payment->created_at->format('d M, Y'),
                                        'status' => 'Paid'
                                    ];
                                });

            // ৬. চার্ট ডাটা: মাসিক ইনকাম
            $incomeData = Payment::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

            // ৭. চার্ট ডাটা: মাসিক খরচ (অপশনাল, চাইলে চার্টে দেখাতে পারো)
            $expenseData = Expense::select(
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->whereYear('expense_date', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

            $monthly_income = [];
            $monthly_expense = [];

            foreach (range(1, 12) as $m) {
                $monthly_income[] = $incomeData[$m] ?? 0;
                $monthly_expense[] = $expenseData[$m] ?? 0;
            }

            // ৮. এটেন্ডেন্স চার্ট
            $attendanceCounts = Attendance::whereDate('date', now()->toDateString())
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

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
                        'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        'income' => $monthly_income,
                        'expense' => $monthly_expense, // চার্টে দেখানোর জন্য পাঠালাম
                        'attendance' => $attendance_ratio
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
