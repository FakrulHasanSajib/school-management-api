<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamMark;
use Illuminate\Support\Facades\DB;

class ExamService
{
    /**
     * ১. নতুন পরীক্ষা তৈরি (যেমন: Midterm 2024)
     */
 public function createExam(array $data)
{
    // সরাসরি $data পাস করুন যাতে session সহ সব যায়
    return \App\Models\Exam::create($data); 
}

    /**
     * ২. পরীক্ষার রুটিন বা শিডিউল তৈরি
     */
    public function createSchedule(array $data)
    {
        return ExamSchedule::create($data); // 
    }

    /**
     * ৩. স্টুডেন্টের মার্কস এন্ট্রি করা (Bulk Entry)
     */
   public function enterMarks(array $data)
{
    return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
        foreach ($data['marks'] as $markData) {
            \App\Models\ExamMark::updateOrCreate(
                [
                    'exam_id' => $data['exam_id'],
                    'student_id' => $markData['student_id'],
                    'subject_id' => $data['subject_id'],
                ],
                [
                    'class_id' => $data['class_id'], // ✅ এটি নিশ্চিত করুন
                    'marks_obtained' => $markData['marks_obtained'],
                    'grade' => $this->calculateGrade($markData['marks_obtained']),
                ]
            );
        }
        return true;
    });
}

// গ্রেড ক্যালকুলেশন লজিক (এটিও যোগ করে দিন)
private function calculateGrade($marks)
{
    if ($marks >= 80) return 'A+';
    if ($marks >= 70) return 'A';
    if ($marks >= 60) return 'A-';
    if ($marks >= 50) return 'B';
    if ($marks >= 40) return 'C';
    if ($marks >= 33) return 'D';
    return 'F';
}
}