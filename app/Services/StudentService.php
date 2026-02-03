<?php

namespace App\Services;

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentService
{
    /**
     * Get All Students
     */
    public function getAllStudents()
    {
        return StudentProfile::with('user', 'schoolClass', 'section')->get();
    }

    /**
     * Create New Student
     */
    public function createStudent(array $data)
    {
        return DB::transaction(function () use ($data) {

            // ✅ ১. ছবি আপলোড লজিক
            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                // 'students' ফোল্ডারে ছবি সেভ হবে (public ডিস্কে)
                $path = $data['image']->store('students', 'public');
                $data['image'] = $path;
            }

            // ✅ ২. ইউজার তৈরি
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],

                // ❌ এই লাইনটি সরানো হয়েছে: 'password' => Hash::make($data['password']),

                // ✅ ডিফল্ট পাসওয়ার্ড সেট করা হলো
                'password' => Hash::make('12345678'),

                'role' => 'student',
                'status' => true,
                'must_change_password' => true, // ✅ ফোর্স পাসওয়ার্ড চেঞ্জ অন
            ]);

            // রোল এসাইন (Spatie Permission যদি থাকে)
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('student');
            }

            // ✅ ৩. প্রোফাইল তৈরি
            return StudentProfile::create([
                'user_id' => $user->id,
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'admission_no' => $data['admission_no'],
                'roll_no' => $data['roll_no'],
                'gender' => $data['gender'],
                'dob' => $data['dob'],
                'address' => $data['address'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'image' => $data['image'] ?? null, // ছবির পাথ
            ]);
        });
    }

    /**
     * Get Students By Section
     */
    public function getStudentsBySection($sectionId)
    {
        return StudentProfile::with('user')->where('section_id', $sectionId)->get();
    }

    /**
     * Get Student By ID
     */
    public function getStudentById($id)
    {
        return StudentProfile::with('user', 'schoolClass', 'section')->findOrFail($id);
    }

    /**
     * Update Student Information
     */
    public function updateStudent($id, array $data)
    {
        $student = StudentProfile::findOrFail($id);

        return DB::transaction(function () use ($student, $data) {

            // ১. ছবি আপডেটের লজিক
            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                // আগের ছবি ডিলিট করা
                if ($student->image) {
                    Storage::disk('public')->delete($student->image);
                }
                // নতুন ছবি আপলোড
                $path = $data['image']->store('students', 'public');
                $data['image'] = $path;
            }

            // ২. ইউজার টেবিল আপডেট (নাম ও ইমেইল)
            $student->user->update([
                'name' => $data['name'],
                'email' => $data['email']
            ]);

            // ৩. প্রোফাইল টেবিল আপডেট
            $student->update([
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'roll_no' => $data['roll_no'],
                'gender' => $data['gender'],
                'dob' => $data['dob'],
                'address' => $data['address'],
                'phone' => $data['phone'] ?? $student->phone,
                'blood_group' => $data['blood_group'] ?? $student->blood_group,
                'image' => $data['image'] ?? $student->image,
            ]);

            return $student;
        });
    }
}
