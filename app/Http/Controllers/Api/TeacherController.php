<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TeacherService;
use App\Http\Requests\StoreTeacherRequest;
use Illuminate\Http\Request; // ✅ এটি মিসিং ছিল
use App\Models\User;         // ✅ এটি মিসিং ছিল
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;
use App\Models\TeacherProfile;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB as FacadesDB;

 // ট্রানজেকশনের জন্য

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
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'designation' => 'required|string',
        'phone' => 'required|string',
        'image' => 'nullable|image|max:2048', 
        'blood_group' => 'nullable|string',
        'qualification' => 'required|string',
        'joining_date' => 'required|date',
    ]);

    try {
        DB::transaction(function () use ($request, $id) {
            $user = User::findOrFail($id);

            // User টেবিল আপডেট
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // ১. আগে সব ডাটা একটি অ্যারেতে গুছিয়ে নিন
            $updateData = [
                'designation' => $request->designation,
                'qualification' => $request->qualification,
                'phone' => $request->phone,
                'joining_date' => $request->joining_date,
                'blood_group' => $request->blood_group,
            ];

            // ২. যদি নতুন ছবি থাকে, তবে সেটা প্রোসেস করুন
            if ($request->hasFile('image')) {
                // পুরনো ছবি থাকলে ডিলিট করুন
                if ($user->teacherProfile && $user->teacherProfile->image) {
                    Storage::disk('public')->delete($user->teacherProfile->image);
                }
                // নতুন ছবির পাথ অ্যারেতে যোগ করুন
                $updateData['image'] = $request->file('image')->store('teachers', 'public');
            }

            // ৩. এবার একসাথে সব ডাটা আপডেট করুন
            if ($user->teacherProfile) {
                $user->teacherProfile()->update($updateData);
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