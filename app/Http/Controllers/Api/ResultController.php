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
    /**
     * ১. নির্দিষ্ট স্টুডেন্টের রেজাল্ট জেনারেট করা (Single Result)
     */
    public function getStudentResult($exam_id, $student_id): JsonResponse
    {
        // ১. স্টুডেন্ট এবং এক্সাম খুঁজে বের করা (User Relation সহ)
        $student = StudentProfile::with(['user', 'schoolClass', 'section'])->find($student_id);
        $exam = Exam::find($exam_id);

        if (!$student || !$exam) {
            return response()->json([
                'status' => false,
                'message' => 'Student or Exam not found'
            ], 404);
        }

        // ২. মার্কস খুঁজে বের করা (Subject সহ)
        $marks = ExamMark::with('subject')
            ->where('exam_id', $exam_id)
            ->where('student_id', $student_id)
            ->get();

        if ($marks->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Result not found for this exam.'
            ], 200);
        }

        // ৩. ক্যালকুলেশন ভেরিয়েবল
        $totalMarks = 0;
        $totalGpa = 0;
        $totalSubjects = $marks->count();
        $isFail = false;
        $results = [];

        // ৪. লুপ চালিয়ে ক্যালকুলেশন
        foreach ($marks as $mark) {
            $obtained = $mark->marks_obtained;
            $gradeInfo = $this->calculateGrade($obtained);

            if ($gradeInfo['grade'] === 'F') {
                $isFail = true;
            }

            $totalMarks += $obtained;
            $totalGpa += $gradeInfo['gpa'];

            $results[] = [
                'id' => $mark->id,
                'subject' => [
                    'name' => $mark->subject->name ?? 'Unknown Subject'
                ],
                'marks_obtained' => $obtained,
                'grade' => $gradeInfo['grade'],
                'gpa' => $gradeInfo['gpa'],
            ];
        }

        // ৫. ফাইনাল জিপিএ এবং গ্রেড বের করা
        $finalGpa = ($totalSubjects > 0 && !$isFail) ? ($totalGpa / $totalSubjects) : 0.00;
        $finalGrade = $this->getFinalGrade($finalGpa, $isFail);

        // ৬. রেসপন্স পাঠানো
        return response()->json([
            'status' => true,
            'data' => [
                'student' => $student, // এখানে user, class, section সব থাকবে
                'exam' => $exam,
                'marks' => $results,
                'total_marks' => $totalMarks,
                'final_gpa' => number_format($finalGpa, 2),
                'final_grade' => $finalGrade,
                'result_status' => $isFail ? 'FAIL' : 'PASS'
            ]
        ]);
    }

    /**
     * ২. পুরো সেকশনের ট্যাবুলেশন শিট (Tabulation Sheet)
     */
    public function getTabulationSheet($exam_id, $section_id): JsonResponse
    {
        // ১. সেকশনের সব স্টুডেন্ট লোড করা
        $students = StudentProfile::with('user')
            ->where('section_id', $section_id)
            ->orderBy('roll_no', 'asc')
            ->get();

        if ($students->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No students found in this section.'], 200);
        }

        // ২. সব মার্কস একবারে আনা (Optimization)
        $allMarks = ExamMark::with('subject')
            ->where('exam_id', $exam_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $tabulation = [];

        // ৩. প্রতি স্টুডেন্টের জন্য লুপ
        foreach ($students as $student) {
            $studentMarks = $allMarks[$student->id] ?? collect([]);

            $totalMarks = 0;
            $totalGpa = 0;
            $totalSubjects = $studentMarks->count();
            $isFail = ($totalSubjects === 0) ? true : false;
            $subjectBreakdown = [];

            foreach ($studentMarks as $mark) {
                $obtained = $mark->marks_obtained;
                $gradeInfo = $this->calculateGrade($obtained);

                if ($gradeInfo['grade'] === 'F') $isFail = true;

                $totalMarks += $obtained;
                $totalGpa += $gradeInfo['gpa'];

                $subjectBreakdown[] = [
                    'subject' => $mark->subject->name ?? 'Unknown',
                    'marks' => $obtained,
                    'grade' => $gradeInfo['grade']
                ];
            }

            // ফাইনাল রেজাল্ট
            $finalGpa = ($totalSubjects > 0 && !$isFail) ? ($totalGpa / $totalSubjects) : 0.00;
            $finalGrade = $this->getFinalGrade($finalGpa, $isFail);
            $status = ($isFail || $finalGrade === 'F') ? 'FAIL' : 'PASS';

            $tabulation[] = [
                'student_id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
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

    /**
     * মার্কস থেকে গ্রেড এবং জিপিএ বের করার হেল্পার ফাংশন
     */
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

    /**
     * জিপিএ থেকে ফাইনাল গ্রেড বের করার হেল্পার ফাংশন
     */
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
