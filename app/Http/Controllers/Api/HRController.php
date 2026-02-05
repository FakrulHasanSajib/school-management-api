<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\Payroll;
use Illuminate\Support\Facades\Validator; // ✅ Validator অ্যাড করা হয়েছে

class HRController extends Controller
{
    // ১. ডেজিগনেশন তৈরি (এটা ঠিক আছে)
    public function storeDesignation(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|unique:designations,name',
            'basic_salary' => 'required|numeric'
        ]);

        $designation = Designation::create($validated);
        return response()->json(['message' => 'Designation created', 'data' => $designation], 201);
    }

    // ২. স্যালারি পেমেন্ট (✅ ফ্রন্টএন্ডের সাথে মিল রেখে আপডেট করা হয়েছে)
    public function paySalary(Request $request) {
        // ভ্যালিডেশন
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|exists:users,id', // ফ্রন্টএন্ড পাঠাচ্ছে teacher_id
            'salary_month' => 'required',               // ফ্রন্টএন্ড পাঠাচ্ছে salary_month
            'amount' => 'required|numeric',
            'bonus' => 'nullable|numeric',
            'deduction' => 'nullable|numeric',
            'note' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // নেট স্যালারি হিসাব
            $basic = $request->amount;
            $bonus = $request->bonus ?? 0;
            $deduction = $request->deduction ?? 0;
            $net_salary = ($basic + $bonus) - $deduction;

            // ডাটাবেসে সেভ
            $payroll = Payroll::create([
                'user_id' => $request->teacher_id,
                'salary_month' => $request->salary_month,
                'amount' => $basic,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'net_salary' => $net_salary,
                'payment_date' => now(),
                'status' => 'Paid',
                'note' => $request->note
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Salary paid successfully!',
                'data' => $payroll
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getPayrollHistory()
{
    // লেটেস্ট পেমেন্ট আগে দেখাবে, সাথে ইউজারের নাম ও ডেজিগনেশন থাকবে
    $history = \App\Models\Payroll::with('user.teacherProfile')->latest()->get();

    return response()->json([
        'status' => true,
        'data' => $history
    ]);
}
// HRController.php এর ভেতরে

// ১. সব ছুটির আবেদন দেখা (অ্যাডমিনের জন্য)
public function getLeaves()
{
    // ইউজার রিলেশনসহ সব ছুটির লিস্ট লোড করা হচ্ছে
    $leaves = \App\Models\Leave::with('user')->latest()->get();

    return response()->json([
        'status' => true,
        'data' => $leaves
    ]);
}

// ২. ছুটির স্ট্যাটাস আপডেট করা (Approve/Reject)
public function updateLeaveStatus(\Illuminate\Http\Request $request, $id)
{
    $leave = \App\Models\Leave::find($id);

    if (!$leave) {
        return response()->json(['status' => false, 'message' => 'Leave record not found'], 404);
    }

    // স্ট্যাটাস আপডেট (Approved অথবা Rejected)
    $leave->status = $request->status;
    $leave->save();

    return response()->json([
        'status' => true,
        'message' => 'Leave status updated to ' . $request->status
    ]);
}
}
