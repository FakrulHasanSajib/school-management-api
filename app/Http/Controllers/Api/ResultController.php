<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamMark;
use App\Models\Exam;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;

class ResultController extends Controller
{
    // ১. স্টুডেন্টের রেজাল্ট জেনারেট করা (সিঙ্গেল)
    public function getStudentResult($exam_id, $student_id): JsonResponse
    {
        // মার্কস খুঁজে বের করা
        $marks = ExamMark::with('subject')
            ->where('exam_id', $exam_id)
            ->where('student_id', $student_id)
            ->get();

        if ($marks->isEmpty()) {
            return response()->json([
                'status' => false, 
                'message' => 'এই ছাত্রের রেজাল্ট তৈরি হয়নি (মার্কস এন্ট্রি করা হয়নি)।'
            ], 200);
        }

        // ছাত্র এবং পরীক্ষার তথ্য আনা
        // ✅ ফিক্স: ইউজারের নাম আনার জন্য 'user' রিলেশন লোড করা হচ্ছে
        $student = StudentProfile::with('user')->find($student_id);
        $exam = Exam::find($exam_id);

        // ক্যালকুলেশন শুরু
        $totalMarks = 0;
        $totalGpa = 0;
        $totalSubjects = $marks->count();
        $isFail = false;
        $results = [];

        foreach ($marks as $mark) {
            $obtained = $mark->marks_obtained;
            $gradeInfo = $this->calculateGrade($obtained);

            if ($gradeInfo['grade'] === 'F') {
                $isFail = true;
            }

            $totalMarks += $obtained;
            $totalGpa += $gradeInfo['gpa'];

            $results[] = [
                'subject' => $mark->subject->name ?? 'Unknown',
                'marks' => $obtained,
                'grade' => $gradeInfo['grade'],
                'gpa' => $gradeInfo['gpa'],
            ];
        }

        $finalGpa = ($totalSubjects > 0 && !$isFail) ? ($totalGpa / $totalSubjects) : 0.00;
        $finalGrade = $this->getFinalGrade($finalGpa, $isFail);

        return response()->json([
            'status' => true,
            'data' => [
                'student_name' => $student->user->name ?? 'Unknown', // ✅ নাম ফিক্স
                'student_roll' => $student->roll_no ?? 'N/A',
                'exam_name' => $exam->name ?? 'Unknown',
                'details' => $results,
                'summary' => [
                    'total_marks' => $totalMarks,
                    'final_gpa' => number_format($finalGpa, 2),
                    'final_grade' => $finalGrade,
                    'result_status' => $isFail ? 'FAIL' : 'PASS'
                ]
            ]
        ]);
    }

    // ২. পুরো সেকশনের ট্যাবুলেশন শিট (Tabulation Sheet)
    public function getTabulationSheet($exam_id, $section_id): JsonResponse
    {
        // ✅ ফিক্স: 'user' রিলেশনসহ স্টুডেন্ট আনা হচ্ছে যাতে নাম পাওয়া যায়
        $students = StudentProfile::with('user')
            ->where('section_id', $section_id)
            ->orderBy('roll_no', 'asc')
            ->get();

        if ($students->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'এই সেকশনে কোনো ছাত্র পাওয়া যায়নি।'], 200);
        }

        $allMarks = ExamMark::with('subject')
            ->where('exam_id', $exam_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $tabulation = [];

        foreach ($students as $student) {
            // ✅ নামের ফিক্স: User টেবিল থেকে নাম নেওয়া হচ্ছে
            $studentName = $student->user->name ?? 'Unknown';

            $marks = $allMarks[$student->id] ?? collect([]);
            
            $totalMarks = 0;
            $totalGpa = 0;
            $totalSubjects = $marks->count();
            
            // ✅ লজিক ফিক্স: মার্কস না থাকলে শুরুতেই ফেইল ধরা হবে
            $isFail = ($totalSubjects === 0) ? true : false;
            
            $subjectBreakdown = [];

            foreach ($marks as $mark) {
                $obtained = $mark->marks_obtained;
                $gradeInfo = $this->calculateGrade($obtained);

                // যদি কোনো বিষয়ে F পায়, তবে ফেইল
                if ($gradeInfo['grade'] === 'F') $isFail = true;

                $totalMarks += $obtained;
                $totalGpa += $gradeInfo['gpa'];

                $subjectBreakdown[] = [
                    'subject' => $mark->subject->name,
                    'marks' => $obtained,
                    'grade' => $gradeInfo['grade']
                ];
            }

            // ফাইনাল ক্যালকুলেশন
            $finalGpa = ($totalSubjects > 0 && !$isFail) ? ($totalGpa / $totalSubjects) : 0.00;
            $finalGrade = $this->getFinalGrade($finalGpa, $isFail); // F থাকলে এখানে F রিটার্ন করবে

            // ✅ স্ট্যাটাস ফিক্স: নিশ্চিত করা হচ্ছে F গ্রেড মানেই FAIL
            $status = ($isFail || $finalGrade === 'F') ? 'FAIL' : 'PASS';

            $tabulation[] = [
                'student_id' => $student->id,
                'name' => $studentName,
                'roll' => $student->roll_no,
                'subjects' => $subjectBreakdown,
                'total_marks' => $totalMarks,
                'gpa' => number_format($finalGpa, 2),
                'grade' => $finalGrade,
                'status' => $status
            ];
        }

        return response()->json(['status' => true, 'data' => $tabulation]);
    }

    // হেল্পার ফাংশন: মার্কস থেকে গ্রেড বের করা
    private function calculateGrade($marks)
    {
        if ($marks >= 80) return ['grade' => 'A+', 'gpa' => 5.00];
        if ($marks >= 70) return ['grade' => 'A', 'gpa' => 4.00];
        if ($marks >= 60) return ['grade' => 'A-', 'gpa' => 3.50];
        if ($marks >= 50) return ['grade' => 'B', 'gpa' => 3.00];
        if ($marks >= 40) return ['grade' => 'C', 'gpa' => 2.00];
        if ($marks >= 33) return ['grade' => 'D', 'gpa' => 1.00];
        return ['grade' => 'F', 'gpa' => 0.00];
    }

    // হেল্পার ফাংশন: জিপিএ থেকে ফাইনাল গ্রেড
    private function getFinalGrade($gpa, $isFail)
    {
        if ($isFail) return 'F';
        if ($gpa >= 5.00) return 'A+';
        if ($gpa >= 4.00) return 'A';
        if ($gpa >= 3.50) return 'A-';
        if ($gpa >= 3.00) return 'B';
        if ($gpa >= 2.00) return 'C';
        if ($gpa >= 1.00) return 'D';
        return 'F';
    }
}