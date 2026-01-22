<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\Staff;
use App\Models\Payroll;

class HRController extends Controller
{
    // ১. ডেজিগনেশন তৈরি
    public function storeDesignation(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|unique:designations,name',
            'basic_salary' => 'required|numeric'
        ]);

        $designation = Designation::create($validated);
        return response()->json(['message' => 'Designation created', 'data' => $designation], 201);
    }

    // ২. স্যালারি পেমেন্ট
    public function paySalary(Request $request) {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'month' => 'required|string',
            'year' => 'required|integer',
            'amount' => 'required|numeric',
            'status' => 'required|string'
        ]);

        $payroll = Payroll::create($validated);
        return response()->json(['message' => 'Salary paid successfully', 'data' => $payroll], 201);
    }
}