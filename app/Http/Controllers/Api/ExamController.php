<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Exam;
use App\Models\ExamMark; // ✅ এই লাইনটি মিসিং ছিল

class ExamController extends Controller
{
    // ১. সব পরীক্ষার লিস্ট দেখা
    public function index(): JsonResponse
    {
        $exams = Exam::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $exams], 200);
    }

    // ২. নতুন পরীক্ষা তৈরি
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
        ]);

        $exam = Exam::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date ?? $request->start_date,
            'class_id' => $request->class_id ?? 1,
            'session' => $request->session ?? date('Y'),
            'is_active' => true
        ]);

        return response()->json(['status' => true, 'message' => 'Exam created successfully'], 201);
    }

    // ৩. পরীক্ষা আপডেট করা
    public function update(Request $request, $id): JsonResponse
    {
        $exam = Exam::find($id);
        if (!$exam) {
            return response()->json(['status' => false, 'message' => 'Exam not found'], 404);
        }

        $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
        ]);

        $exam->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'class_id' => $request->class_id,
            'session' => $request->session,
        ]);

        return response()->json(['status' => true, 'message' => 'Exam updated successfully']);
    }

    // ৪. মার্কস সেভ করা (✅ এই ফাংশনটি আপনার ফাইলে ছিল না)
    public function storeMarks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_id' => 'required',
            'class_id' => 'required',
            'subject_id' => 'required',
            'marks' => 'required|array',
        ]);

        foreach ($validated['marks'] as $markData) {
            // ExamMark মডেল ব্যবহার করে ডাটা সেভ বা আপডেট করা হচ্ছে
            ExamMark::updateOrCreate(
                [
                    'exam_id' => $validated['exam_id'],
                    'student_id' => $markData['student_id'],
                    'subject_id' => $validated['subject_id']
                ],
                [
                    'class_id' => $validated['class_id'],
                    'marks_obtained' => $markData['marks_obtained']
                ]
            );
        }

        return response()->json(['status' => true, 'message' => 'Marks submitted successfully'], 201);
    }

    // ৫. ডিলিট করা
    public function destroy($id): JsonResponse
    {
        $exam = Exam::find($id);
        if ($exam) {
            $exam->delete();
            return response()->json(['status' => true, 'message' => 'Exam deleted successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Exam not found'], 404);
    }
}