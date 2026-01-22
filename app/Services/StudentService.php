<?php

namespace App\Services;

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentService
{
    public function getAllStudents()
    {
        return StudentProfile::with('user', 'schoolClass', 'section')->get();
    }

    public function createStudent(array $data)
    {
        return DB::transaction(function () use ($data) {
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
                // অন্যান্য ফিল্ড...
            ]);

            return $student;
        });
    }
}