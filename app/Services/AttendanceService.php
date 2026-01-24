<?php

namespace App\Services;

use App\Models\Attendance; // ⚠️ লক্ষ্য করুন: এখানে Attendance মডেল ব্যবহার করা হয়েছে
use Illuminate\Support\Facades\DB;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Http;

class AttendanceService
{
    /**
     * বাল্ক অ্যাটেনডেন্স নেওয়া
     */
// app/Services/AttendanceService.php
public function storeAttendance(array $data)
{
    return DB::transaction(function () use ($data) {
        foreach ($data['attendances'] as $attendanceData) {
            Attendance::updateOrCreate(
                ['student_id' => $attendanceData['student_id'], 'date' => $data['date']],
                [
                    'class_id' => $data['class_id'],
                    'section_id' => $data['section_id'],
                    'status' => $attendanceData['status'],
                    'remarks' => $attendanceData['remarks'] ?? null, 
                ]
            );

            if ($attendanceData['status'] === 'Absent') {
                $this->sendAbsentSMS($attendanceData['student_id'], $data['date']);
            }
        }
    });
}

protected function sendAbsentSMS($studentId, $date) 
{
    // ১. স্টুডেন্টের বাবার নম্বর এবং তথ্য নিন
    $student = StudentProfile::with('user')->find($studentId);
    $phone = $student->parent_phone; // আপনার টেবিলে ফোন নম্বর কলামের নাম যা আছে
    $name = $student->user->name;

    // ২. এসএমএস মেসেজ তৈরি
    $message = "প্রিয় অভিভাবক, আপনার সন্তান $name আজ $date স্কুলে অনুপস্থিত। - সফটওয়্যার আইটি স্কুল";

    // ৩. এসএমএস গেটওয়ে (যেমন: SSL Wireless/BulkSMSBD) এপিআই কল
    // Http::get("https://api.sms-gateway.com/send", [
    //    'api_key' => 'YOUR_API_KEY',
    //    'to' => $phone,
    //    'msg' => $message
    // ]);
}

    /**
     * রিপোর্ট দেখা
     */
    // app/Services/AttendanceService.php

// app/Services/AttendanceService.php

public function getMonthlyReport($classId, $sectionId, $month, $year)
{
    // ওই মাসের শুরু এবং শেষ তারিখ বের করা
    $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $endDate = date("Y-m-t", strtotime($startDate));

    // স্টুডেন্টদের সাথে ওই মাসের হাজিরার ডাটা নিয়ে আসা
    return \App\Models\StudentProfile::with(['user', 'attendances' => function($query) use ($startDate, $endDate) {
        $query->whereBetween('date', [$startDate, $endDate]);
    }])
    ->where('class_id', $classId)
    ->where('section_id', $sectionId)
    ->get()
    ->map(function($student) {
        // প্রতিটি স্টুডেন্টের জন্য প্রেজেন্ট, অ্যাবসেন্ট এবং লেট গণনা
        return [
            'id' => $student->id,
            'name' => $student->user->name,
            'roll_no' => $student->roll_no,
            'present_count' => $student->attendances->where('status', 'Present')->count(),
            'absent_count' => $student->attendances->where('status', 'Absent')->count(),
            'late_count' => $student->attendances->where('status', 'Late')->count(),
        ];
    });
}

// app/Services/AttendanceService.php

public function getStudentAttendanceSummary($studentId, $month, $year)
{
    $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $endDate = date("Y-m-t", strtotime($startDate));

    $student = \App\Models\StudentProfile::with(['user', 'attendances' => function($query) use ($startDate, $endDate) {
        $query->whereBetween('date', [$startDate, $endDate]);
    }])->findOrFail($studentId);

    return [
        'student_info' => [
            'name' => $student->user->name,
            'roll_no' => $student->roll_no,
            'class' => $student->schoolClass->name ?? 'N/A', //
        ],
        'summary' => [
            'total_working_days' => $student->attendances->count(),
            'present' => $student->attendances->where('status', 'Present')->count(),
            'absent' => $student->attendances->where('status', 'Absent')->count(),
            'late' => $student->attendances->where('status', 'Late')->count(),
        ],
        'daily_logs' => $student->attendances->map(function($att) {
            return [
                'date' => $att->date,
                'status' => $att->status,
                'remarks' => $att->remarks // কার মাধ্যমে হাজিরা নেওয়া হয়েছে
            ];
        })
    ];
}

}