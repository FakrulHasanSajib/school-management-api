<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TeacherService;
use App\Http\Requests\StoreTeacherRequest;
use Illuminate\Http\JsonResponse;

class TeacherController extends Controller
{
    use \App\Traits\ApiResponse;
    protected $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    // সব শিক্ষকের লিস্ট দেখার API
    public function index(): JsonResponse
    {
        $teachers = $this->teacherService->getAllTeachers();
        return $this->success($teachers, 'Teachers list fetched successfully');
    }

    // শিক্ষক তৈরি করার API
    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $teacher = $this->teacherService->createTeacher($request->validated());
        return $this->success($teacher, 'Teacher created successfully', 201);
    }
}