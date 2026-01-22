<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\Leave;

class HRService
{
    /**
     * ১. মাসের বেতন জেনারেট করা
     */
    public function generateMonthlyPayroll(array $data)
    {
        return Payroll::create([
            'user_id' => $data['user_id'],
            'month' => $data['month'],
            'basic_salary' => $data['basic_salary'],
            'bonus' => $data['bonus'] ?? 0,
            'total_paid' => $data['basic_salary'] + ($data['bonus'] ?? 0),
            'status' => 'Paid'
        ]);
    }

    /**
     * ২. ছুটির আবেদন করা
     */
    public function applyForLeave(array $data)
    {
        return Leave::create([
            'user_id' => $data['user_id'],
            'type' => $data['type'], // Sick, Casual etc. 
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'Pending'
        ]);
    }

    /**
     * ৩. ছুটির আবেদন অনুমোদন বা বাতিল করা
     */
    public function updateLeaveStatus($leaveId, $status)
    {
        $leave = Leave::findOrFail($leaveId);
        $leave->update(['status' => $status]); // Approved or Rejected 
        return $leave;
    }
}