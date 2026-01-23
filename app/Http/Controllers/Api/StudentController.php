<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Resources\StudentResource;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    /**
     * Delete Student
     */
    public function destroy($id): JsonResponse
    {
        // ১. স্টুডেন্ট খুঁজে বের করা (আপনার সার্ভিস ব্যবহার করে)
        $student = $this->studentService->getStudentById($id);

        if (!$student) {
            return $this->error('Student not found', 404);
        }

        // ২. ইউজার এবং স্টুডেন্ট ডাটা ডিলিট করা
        // (আগে ইউজার ডিলিট করলে ফরেন কি এরর আসতে পারে, তাই বুঝে করতে হবে)
        
        $user = $student->user; // ইউজারের তথ্য নেওয়া
        $student->delete();     // আগে স্টুডেন্ট প্রোফাইল ডিলিট
        
        if ($user) {
            $user->delete();    // এরপর মেইন লগইন একাউন্ট ডিলিট
        }

        return $this->success(null, 'Student deleted successfully');
    }
    // StudentController.php এর ভেতরে

// StudentController.php এর ভেতরে যোগ করুন

public function getNextNumbers(Request $request): JsonResponse
{
    // ১. পরবর্তী অ্যাডমিশন নম্বর (এটা গ্লোবাল সিরিয়াল)
    $lastStudent = \App\Models\StudentProfile::latest('id')->first();
    $nextId = $lastStudent ? $lastStudent->id + 1 : 1;
    $nextAdmissionNo = 'ADM-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

    // ২. পরবর্তী রোল নম্বর (ক্লাস এবং সেকশন অনুযায়ী)
    $nextRollNo = 1;
    
    // যদি ক্লাস এবং সেকশন দুটোই পাঠানো হয়
    if ($request->class_id && $request->section_id) {
        $lastRoll = \App\Models\StudentProfile::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->max('roll_no');
            
        $nextRollNo = $lastRoll ? (int)$lastRoll + 1 : 1;
    }

    return response()->json([
        'next_admission_no' => $nextAdmissionNo,
        'next_roll_no' => $nextRollNo
    ]);
}
}