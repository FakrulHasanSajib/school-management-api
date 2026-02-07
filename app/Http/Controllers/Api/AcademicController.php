<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AcademicService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse; // ✅ ট্রেইট ইমপোর্ট করুন

class AcademicController extends Controller
{
    use ApiResponse; // ✅ ট্রেইট ব্যবহার করুন

    protected $academicService;

    public function __construct(AcademicService $academicService)
    {
        $this->academicService = $academicService;
    }

    /**
     * ✅ API.php এর সাথে মিল রেখে নাম পরিবর্তন: index -> indexClass
     */
    public function indexClass(): JsonResponse
    {
        // এটি ক্লাস এবং তার সেকশনগুলো একসাথে নিয়ে আসবে
        $classes = $this->academicService->getAllClassesWithSections();

        return $this->success($classes, 'Academic classes fetched successfully');
    }

    /**
     * ✅ নতুন মেথড: সব সেকশন দেখার জন্য (স্টুডেন্ট ফর্মের জন্য লাগবে)
     */
    public function indexSection(): JsonResponse
    {
        // যদি সার্ভিসে getAllSections না থাকে তবে সরাসরি মডেল ব্যবহার করতে পারেন
        $sections = \App\Models\Section::all();

        return $this->success($sections, 'All sections fetched successfully');
    }

    public function storeClass(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:classes,name',
            'numeric_value' => 'required|integer'
        ]);

        $class = $this->academicService->createClass($validated);
        return $this->success($class, 'Class created successfully', 201);
    }

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
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string',
            'code' => 'required|string|unique:subjects,code',
            'type' => 'required|in:Theory,Practical'
        ]);

        $subject = $this->academicService->createSubject($validated);
        return $this->success($subject, 'Subject created successfully', 201);
    }

    public function getSubjects($classId): JsonResponse
    {
        $subjects = $this->academicService->getSubjectsByClass($classId);
        return $this->success($subjects, 'Subjects fetched successfully');
    }
    /**
 * ✅ নির্দিষ্ট ক্লাসের আন্ডারে থাকা সেকশনগুলো দেখার জন্য
 */
public function getSectionsByClass($classId): JsonResponse
{
    // সরাসরি সেকশন মডেল থেকে ফিল্টার করে ডাটা আনা
    $sections = \App\Models\Section::where('class_id', $classId)->get();

    return $this->success($sections, 'Sections for this class fetched successfully');
}

// AcademicController.php এর ভেতরে

public function assignTeacher(Request $request)
{
    // ১. ভ্যালিডেশন
    $request->validate([
        'section_id' => 'required|exists:sections,id',
        'teacher_id' => 'required|exists:users,id' // অথবা teachers টেবিলে থাকলে teachers,id
    ]);

    // ২. সেকশন খুঁজে বের করা
    $section = \App\Models\Section::findOrFail($request->section_id);

    // ৩. টিচার অ্যাসাইন করা
    $section->teacher_id = $request->teacher_id;
    $section->save();

    return response()->json([
        'status' => true,
        'message' => 'Class Teacher assigned successfully!'
    ]);
}
}
