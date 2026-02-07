<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentProfile;
use App\Models\ExamMark;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class TeacherMarkController extends Controller
{
    // ১. প্রয়োজনীয় ড্রপডাউন ডাটা (Exams & Subjects)
    public function getInitData($classId)
    {
        $exams = Exam::where('is_active', true)->select('id', 'name')->get();
        // ক্লাস অনুযায়ী সাবজেক্ট আনা
        $subjects = Subject::where('class_id', $classId)->select('id', 'name', 'code')->get();

        return response()->json([
            'exams' => $exams,
            'subjects' => $subjects
        ]);
    }

    // ২. মার্কশিট জেনারেট করা (স্টুডেন্ট লিস্ট + আগের মার্কস)
    public function getMarksSheet(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'section_id' => 'required',
            'exam_id' => 'required',
            'subject_id' => 'required',
        ]);

        $students = StudentProfile::with('user:id,name')
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->get()
            ->map(function($student) use ($request) {
                // আগের মার্কস আছে কিনা চেক করা
                $mark = ExamMark::where('student_id', $student->user_id)
                        ->where('exam_id', $request->exam_id)
                        ->where('subject_id', $request->subject_id)
                        ->first();

                return [
                    'student_id' => $student->user_id,
                    'name' => $student->user->name,
                    'roll' => $student->roll_no,
                    'marks_obtained' => $mark ? $mark->marks_obtained : '', // মার্ক না থাকলে খালি
                ];
            });

        return response()->json($students);
    }

    // ৩. মার্কস সেভ করা
    public function storeMarks(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'subject_id' => 'required',
            'class_id' => 'required',
            'marks' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->marks as $item) {
                // মার্ক ভ্যালু ভ্যালিডেট করা (ফাঁকা থাকলে ০ বা স্কিপ)
                $score = is_numeric($item['marks_obtained']) ? $item['marks_obtained'] : 0;

                ExamMark::updateOrCreate(
                    [
                        'exam_id' => $request->exam_id,
                        'student_id' => $item['student_id'],
                        'subject_id' => $request->subject_id
                    ],
                    [
                        'class_id' => $request->class_id,
                        'marks_obtained' => $score
                    ]
                );
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Marks updated successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
