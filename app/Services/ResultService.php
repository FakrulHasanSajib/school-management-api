<?php

namespace App\Services;

use App\Models\ExamMark;
use App\Models\StudentProfile;
use App\Models\Exam;

class ResultService
{
    public function generateReportCard($examId, $studentId)
    {
        $marks = ExamMark::with('subject')
                ->where('exam_id', $examId)
                ->where('student_id', $studentId)
                ->get();

        $student = StudentProfile::with('user')->find($studentId);
        $exam = Exam::find($examId);

        $totalMarks = 0;
        $totalGPA = 0;
        $subjectCount = $marks->count();
        $results = [];
        $isFail = false;

        foreach ($marks as $mark) {
            $gradeInfo = $this->calculateGrade($mark->marks_obtained, 100); // ১০০ মার্ক ধরে হিসাব
            
            $results[] = [
                'subject' => $mark->subject->name,
                'marks' => $mark->marks_obtained,
                'grade' => $gradeInfo['grade'],
                'grade_point' => $gradeInfo['point']
            ];

            $totalMarks += $mark->marks_obtained;
            $totalGPA += $gradeInfo['point'];

            if ($gradeInfo['point'] == 0) {
                $isFail = true;
            }
        }

        $finalGPA = $subjectCount > 0 ? round($totalGPA / $subjectCount, 2) : 0;
        
        if ($isFail) {
            $finalGPA = 0.00; // কোনো এক সাবজেক্টে ফেল করলে মোট GPA ০
        }

        return [
            'student_name' => $student->user->name ?? 'Unknown',
            'exam_name' => $exam->name,
            'results' => $results,
            'total_marks' => $totalMarks,
            'final_gpa' => $finalGPA,
            'final_grade' => $this->getFinalGrade($finalGPA)
        ];
    }

    private function calculateGrade($marks, $total)
    {
        // ডাইনামিক গ্রেডিং লজিক (National Curriculum অনুযায়ী)
        $percentage = ($marks / $total) * 100;

        if ($percentage >= 80) return ['grade' => 'A+', 'point' => 5.00];
        if ($percentage >= 70) return ['grade' => 'A', 'point' => 4.00];
        if ($percentage >= 60) return ['grade' => 'A-', 'point' => 3.50];
        if ($percentage >= 50) return ['grade' => 'B', 'point' => 3.00];
        if ($percentage >= 40) return ['grade' => 'C', 'point' => 2.00];
        if ($percentage >= 33) return ['grade' => 'D', 'point' => 1.00];
        return ['grade' => 'F', 'point' => 0.00];
    }

    private function getFinalGrade($gpa)
    {
        if ($gpa >= 5.00) return 'A+';
        if ($gpa >= 4.00) return 'A';
        if ($gpa >= 3.50) return 'A-';
        if ($gpa >= 3.00) return 'B';
        if ($gpa >= 2.00) return 'C';
        if ($gpa >= 1.00) return 'D';
        return 'F';
    }
}