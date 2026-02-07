<?php

namespace App\Services;

use App\Models\User;
use App\Models\TeacherProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherService
{
    public function createTeacher(array $data)
    {
        return DB::transaction(function () use ($data) {

            // ১. ছবি আপলোড লজিক
            $imagePath = null;
            if (isset($data['image'])) {
                $imagePath = $data['image']->store('teachers', 'public');
            }

            // ২. ইউজার তৈরি
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'teacher',
                // 'status' => true, // ⚠️ আপনার users টেবিলে 'status' কলাম না থাকলে এই লাইনটি কমেন্ট করে দিন
            ]);

            // ৩. রোল এসাইন (যদি Spatie প্যাকেজ থাকে তবেই এটি কাজ করবে)
            // $user->assignRole('teacher'); // প্যাকেজ না থাকলে এটি কমেন্ট করে দিন, নাহলে এরর দিবে।

            // ৪. প্রোফাইল তৈরি
            TeacherProfile::create([
                'user_id' => $user->id,
                'designation' => $data['designation'],
                'qualification' => $data['qualification'],
                'phone' => $data['phone'],
                'joining_date' => $data['joining_date'],
                'image' => $imagePath,
                // ✅ Null coalescing operator (??) ব্যবহার করুন সেফটির জন্য
                'blood_group' => $data['blood_group'] ?? null,
            ]);

            return $user->load('teacherProfile');
        });
    }

    public function getAllTeachers()
    {
        return User::where('role', 'teacher')->with('teacherProfile')->latest()->get();
    }
}
