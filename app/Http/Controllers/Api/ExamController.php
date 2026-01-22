<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExamService;
use App\Services\ResultService; // ✅ নতুন সার্ভিস ইমপোর্ট
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class ExamController extends Controller
{
    use ApiResponse;

    protected $examService;
    protected $resultService; // ✅ প্রপার্টি যোগ করা হয়েছে

    // ✅ কনস্ট্রাক্টরে ResultService ইনজেক্ট করা হলো
    public function __construct(ExamService $examService, ResultService $resultService)
    {
        $this->examService = $examService;
        $this->resultService = $resultService;
    }

    /**
     * নতুন পরীক্ষা তৈরি (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'class_id' => 'required|exists:classes,id', // 'session' এর বদলে class_id সাধারণত বেশি ব্যবহৃত হয়, আপনার মাইগ্রেশন চেক করবেন
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        // সার্ভিস যদি createExam এ অ্যারে নেয়
        $exam = \App\Models\Exam::create($validated); 

        return $this->success($exam, 'Exam created successfully', 201);
    }

    /**
     * মার্কস এন্ট্রি করা (Teacher)
     */
    public function storeMarks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            // 'class_id' => 'required|exists:classes,id', // মার্কস এন্ট্রিতে ক্লাস আইডি সবসময় জরুরি না হতেও পারে যদি এক্সাম আইডিতে ক্লাস থাকে
            'subject_id' => 'required|exists:subjects,id',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:student_profiles,id',
            'marks.*.marks_obtained' => 'required|numeric|min:0|max:100'
        ]);

        // আমরা এখানে সরাসরি লুপ চালিয়ে মার্কস সেভ করতে পারি অথবা আপনার সার্ভিস ব্যবহার করতে পারি
        foreach ($validated['marks'] as $markData) {
            \App\Models\ExamMark::updateOrCreate(
                [
                    'exam_id' => $validated['exam_id'],
                    'student_id' => $markData['student_id'],
                    'subject_id' => $validated['subject_id']
                ],
                [
                    'marks_obtained' => $markData['marks_obtained']
                ]
            );
        }

        return $this->success(null, 'Marks submitted successfully', 201);
    }

    /**
     * একজন ছাত্রের রেজাল্ট এবং রিপোর্ট কার্ড দেখা (Dynamic GPA Calculation)
     */
    public function getStudentResult($exam_id, $student_id): JsonResponse
    {
        try {
            // ✅ ResultService ব্যবহার করে ডাইনামিক রিপোর্ট তৈরি
            $reportCard = $this->resultService->generateReportCard($exam_id, $student_id);
            
            // রিপোর্ট কার্ড টেস্ট পাসের জন্য সরাসরি অ্যারে রিটার্ন করা হচ্ছে
            return response()->json($reportCard, 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 404); // ডাটা না পেলে 404
        }
    }
}