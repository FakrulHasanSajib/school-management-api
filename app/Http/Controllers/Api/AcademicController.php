<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AcademicService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AcademicController extends Controller
{
    protected $academicService;

    public function __construct(AcademicService $academicService)
    {
        $this->academicService = $academicService;
    }

    // সব ক্লাস দেখার API
    public function index(): JsonResponse
    {
        $classes = $this->academicService->getAllClassesWithSections();
        
        return $this->success($classes, 'Academic classes fetched successfully');
    }

    // ক্লাস তৈরি করার API
    public function storeClass(Request $request): JsonResponse
    {
        // ভ্যালিডেশন (Simple রাখার জন্য এখানে দিলাম, পরে Request ফাইলে নিবেন)
        $validated = $request->validate([
            'name' => 'required|string|unique:classes,name',
            'numeric_value' => 'required|integer'
        ]);

        $class = $this->academicService->createClass($validated);

        return $this->success($class, 'Class created successfully', 201);
    }

    // সেকশন তৈরি করার API
    public function storeSection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string',
            'capacity' => 'nullable|integer'
        ]);

        $section = $this->academicService->addSectionToClass($validated);

        return $this->success($section, 'Section added successfully', 201);
    }
    public function storeSubject(Request $request): JsonResponse
    {
        // ভ্যালিডেশন (Simple Validation)
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string',
            'code' => 'required|string|unique:subjects,code', // সাবজেক্ট কোড ইউনিক হতে হবে 
            'type' => 'required|in:Theory,Practical'
        ]);

        $subject = $this->academicService->createSubject($validated);

        return $this->success($subject, 'Subject created successfully', 201);
    }

    // নির্দিষ্ট ক্লাসের সাবজেক্ট দেখার API
    public function getSubjects($classId): JsonResponse
    {
        $subjects = $this->academicService->getSubjectsByClass($classId);
        
        return $this->success($subjects, 'Subjects fetched successfully');
    }
}