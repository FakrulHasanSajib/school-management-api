<?php

namespace App\Services;

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentService
{
    public function getAllStudents()
    {
        return StudentProfile::with('user', 'schoolClass', 'section')->get();
    }

    public function createStudent(array $data)
    {
        return DB::transaction(function () use ($data) {

        // ✅ ১. ছবি আপলোড লজিক
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            // 'students' ফোল্ডারে ছবি সেভ হবে (public ডিস্কে)
            $path = $data['image']->store('students', 'public');
            $data['image'] = $path;
        }
            // ১. ইউজার তৈরি
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'student',
                'status' => true,
            ]);
            
            $user->assignRole('student');

            // ২. প্রোফাইল তৈরি
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

    public function getStudentsBySection($sectionId)
    {
        return StudentProfile::with('user')->where('section_id', $sectionId)->get();
    }

    public function getStudentById($id)
    {
        return StudentProfile::with('user', 'schoolClass', 'section')->findOrFail($id);
    }

    public function updateStudent($id, array $data)
    {
        $student = StudentProfile::findOrFail($id);
        
        return DB::transaction(function () use ($student, $data) {

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            // আগের ছবি ডিলিট করা
            if ($student->image) {
                Storage::disk('public')->delete($student->image);
            }
            // নতুন ছবি আপলোড
            $path = $data['image']->store('students', 'public');
            $data['image'] = $path;
        }
            // ইউজার আপডেট
            $student->user->update([
                'name' => $data['name'],
                'email' => $data['email']
            ]);

            // প্রোফাইল আপডেট
            $student->update([
               'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'roll_no' => $data['roll_no'],
            'gender' => $data['gender'],
            'dob' => $data['dob'],
            'address' => $data['address'],
            
            // ✅ নতুন ডাটা
            'phone' => $data['phone'] ?? $student->phone,
            'blood_group' => $data['blood_group'] ?? $student->blood_group,
            'image' => $data['image'] ?? $student->image,
                // অন্যান্য ফিল্ড...
            ]);

            return $student;
        });
    }
}