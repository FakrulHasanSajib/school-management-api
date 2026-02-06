<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\Payroll;
use App\Models\Leave; // ✅ Leave মডেল ইম্পোর্ট করা হয়েছে
use Illuminate\Support\Facades\Validator;

class HRController extends Controller
{
    // ১. ডেজিগনেশন তৈরি
    public function storeDesignation(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|unique:designations,name',
            'basic_salary' => 'required|numeric'
        ]);

        $designation = Designation::create($validated);
        return response()->json(['status' => true, 'message' => 'Designation created', 'data' => $designation], 201);
    }

    // ২. স্যালারি পেমেন্ট
    public function paySalary(Request $request) {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|exists:users,id',
            'salary_month' => 'required',
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

    // ৩. স্যালারি হিস্ট্রি দেখা
    public function getPayrollHistory()
    {
        $history = Payroll::with('user.teacherProfile')->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $history
        ]);
    }

    // ✅ ৪. ছুটির আবেদন জমা নেওয়া (NEW FUNCTION)
    public function storeLeave(Request $request) {
        // ভ্যালিডেশন
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $leave = Leave::create([
                'user_id' => $request->user()->id, // লগইন করা ইউজার আইডি
                'type' => $request->type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'reason' => $request->reason,
                'status' => 'Pending' // ডিফল্ট স্ট্যাটাস
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Leave application submitted successfully!',
                'data' => $leave
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ৫. সব ছুটির আবেদন দেখা (অ্যাডমিনের জন্য)
    public function getLeaves()
    {
        $leaves = Leave::with('user')->latest()->get();
        return response()->json([
            'status' => true,
            'data' => $leaves
        ]);
    }

    // ৬. ছুটির স্ট্যাটাস আপডেট করা (Approve/Reject)
    public function updateLeaveStatus(Request $request, $id)
    {
        $leave = Leave::find($id);

        if (!$leave) {
            return response()->json(['status' => false, 'message' => 'Leave record not found'], 404);
        }

        $leave->status = $request->status;
        $leave->save();

        return response()->json([
            'status' => true,
            'message' => 'Leave status updated to ' . $request->status
        ]);
    }
}
