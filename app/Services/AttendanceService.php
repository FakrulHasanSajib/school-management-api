<?php

namespace App\Services;

use App\Models\Attendance; // ⚠️ লক্ষ্য করুন: এখানে Attendance মডেল ব্যবহার করা হয়েছে
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * বাল্ক অ্যাটেনডেন্স নেওয়া
     */
    public function storeAttendance(array $data)
    {
        return DB::transaction(function () use ($data) {
            $attendanceRecords = [];
            
            foreach ($data['attendances'] as $attendanceData) {
                // 👇 এখানে StudentAttendance এর বদলে Attendance ব্যবহার করা হলো
                $attendanceRecords[] = Attendance::updateOrCreate(
                    [
                        'student_id' => $attendanceData['student_id'],
                        'date' => $data['date'],
                    ],
                    [
                        'class_id' => $data['class_id'],
                        'section_id' => $data['section_id'],
                        'status' => $attendanceData['status'],
                    ]
                );
            }
            
            return $attendanceRecords;
        });
    }

    /**
     * রিপোর্ট দেখা
     */
    public function getAttendanceReport($sectionId, $date)
    {
        // 👇 এখানেও Attendance ব্যবহার করা হলো
        return Attendance::where('date', $date)
            ->whereHas('student', function($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->with('student.user') 
            ->get();
    }
}