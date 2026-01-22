<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Resources\StudentResource;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse; // আপনার ApiResponse Trait টি ইমপোর্ট করুন

class StudentController extends Controller
{   /**
     * Admit New Student
     *
     * Create a new student profile along with a user account.
     *
     * @bodyParam name string required The name of the student. Example: Karim Uddin
     * @bodyParam email string required The email of the student. Example: karim@school.com
     * @bodyParam password string required The password for login. Example: password123
     * @bodyParam class_id integer required The ID of the class. Example: 1
     * @bodyParam section_id integer required The ID of the section. Example: 2
     * @bodyParam admission_no string required Unique admission number. Example: ADM-2026-001
     * @bodyParam gender string required 'Male' or 'Female'. Example: Male
     *
     * @response 201 {
     * "status": true,
     * "message": "Student admitted successfully",
     * "data": { "id": 5, "user_id": 10, "class_id": 1, "admission_no": "ADM-2026-001" }
     * }
     */
    use ApiResponse; // Trait ব্যবহার করছি

    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }
    /**
     * Get All Students
     *
     * Retrieve a list of all students with their class and section info.
     *
     * @response 200 {
     * "status": true,
     * "data": [
     * { "id": 1, "admission_no": "1001", "user": { "name": "Rahim" }, "class_name": "Class 10" }
     * ]
     * }
     */
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