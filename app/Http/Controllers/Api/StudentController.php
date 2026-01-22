<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Resources\StudentResource;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse; // আপনার ApiResponse Trait টি ইমপোর্ট করুন

class StudentController extends Controller
{
    use ApiResponse; // Trait ব্যবহার করছি

    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index(): JsonResponse
    {
        $students = $this->studentService->getAllStudents();
        
        return $this->success(
            StudentResource::collection($students), 
            'Student list fetched successfully'
        );
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        // 1. সার্ভিস কল করে ডাটা সেভ করা
        $studentProfile = $this->studentService->createStudent($request->validated());

        // 2. CRITICAL FIX: রিলেশন লোড করা (নাহলে রিসোর্সে 'name on null' এরর খাবেন)
        $studentProfile->load('user');

        // 3. রেসপন্স পাঠানো
        return $this->success(
            new StudentResource($studentProfile),
            'Student admitted successfully',
            201
        );
    }
    
    // নির্দিষ্ট সেকশনের স্টুডেন্ট দেখার জন্য
    public function getBySection($section_id): JsonResponse
    {
        $students = $this->studentService->getStudentsBySection($section_id);
        
        return $this->success(
            StudentResource::collection($students),
            'Section wise students fetched successfully'
        );
    }

    public function show($id): JsonResponse
    {
        $student = $this->studentService->getStudentById($id);
        return $this->success(new StudentResource($student), 'Student details fetched');
    }

    public function update(StoreStudentRequest $request, $id): JsonResponse
    {
        $student = $this->studentService->updateStudent($id, $request->validated());
        $student->load('user');
        
        return $this->success(new StudentResource($student), 'Student updated successfully');
    }
}