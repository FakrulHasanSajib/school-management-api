<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TeacherService;
use App\Http\Requests\StoreTeacherRequest;
use Illuminate\Http\Request; // ✅ এটি মিসিং ছিল
use App\Models\User;         // ✅ এটি মিসিং ছিল
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB; // ট্রানজেকশনের জন্য

class TeacherController extends Controller
{
    // ApiResponse ট্রেইট ব্যবহার করায় রেসপন্স ফরম্যাট সুন্দর হবে
    use \App\Traits\ApiResponse;

    protected $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    /**
     * সব শিক্ষকের লিস্ট দেখার API
     */
    public function index(): JsonResponse
    {
        $teachers = $this->teacherService->getAllTeachers();
        return $this->success($teachers, 'Teachers list fetched successfully');
    }

    /**
     * নতুন শিক্ষক তৈরি করার API
     */
    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $teacher = $this->teacherService->createTeacher($request->validated());
        return $this->success($teacher, 'Teacher created successfully', 201);
    }

    /**
     * নির্দিষ্ট টিচারের তথ্য দেখানো (Show/Edit Page)
     */
    public function show($id): JsonResponse
    {
        try {
            // নির্দিষ্ট আইডি এবং টিচার রোল আছে কি না চেক করা হচ্ছে
            $teacher = User::with('teacherProfile')->findOrFail($id);
            
            return $this->success($teacher, 'Teacher details fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Teacher not found', 404);
        }
    }

    /**
     * টিচারের তথ্য আপডেট করা (Update)
     */
    public function update(Request $request, $id): JsonResponse
    {
        // ১. বেসিক ভ্যালিডেশন (এখানেই রাখা হলো যাতে দ্রুত কাজ করে)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id, // নিজের ইমেইল বাদ দিয়ে ইউনিক চেক
            'designation' => 'required|string',
            'phone' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $user = User::findOrFail($id);

                // User টেবিল আপডেট
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);

                // TeacherProfile টেবিল আপডেট (যদি প্রোফাইল থাকে)
                if ($user->teacherProfile) {
                    $user->teacherProfile()->update([
                        'designation' => $request->designation,
                        'qualification' => $request->qualification,
                        'phone' => $request->phone,
                        'joining_date' => $request->joining_date,
                    ]);
                }
            });

            return $this->success(null, 'Teacher updated successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to update teacher: ' . $e->getMessage(), 500);
        }
    }

    /**
     * শিক্ষক ডিলিট করা (Delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            DB::transaction(function () use ($user) {
                // আগে প্রোফাইল ডিলিট
                if ($user->teacherProfile) {
                    $user->teacherProfile()->delete();
                }
                // তারপর ইউজার ডিলিট
                $user->delete();
            });

            return $this->success(null, 'Teacher deleted successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to delete teacher', 500);
        }
    }
}