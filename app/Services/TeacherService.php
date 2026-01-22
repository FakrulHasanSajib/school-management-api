<?php

namespace App\Services;

use App\Models\User;
use App\Models\TeacherProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherService
{
    /**
     * নতুন শিক্ষক নিবন্ধন (User + Role + Profile)
     */
    public function createTeacher(array $data)
    {
        return DB::transaction(function () use ($data) {
            // ১. ইউজার একাউন্ট তৈরি
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'teacher', // ⚠️ এই লাইনটি মিসিং ছিল, তাই student হয়ে যাচ্ছিল
                'status' => true,    // এটিও যোগ করা ভালো
            ]);

            // ২. রোল এসাইন করা (Spatie)
            $user->assignRole('teacher');

            // ৩. টিচার প্রোফাইল তৈরি
            TeacherProfile::create([
                'user_id' => $user->id,
                'designation' => $data['designation'],
                'qualification' => $data['qualification'],
                'phone' => $data['phone'],
                'joining_date' => $data['joining_date'],
                // যদি gender সেভ করতে চান, তবে এখানে 'gender' => $data['gender'] দিতে হবে
                // এবং StoreTeacherRequest এ gender এর ভ্যালিডেশন যোগ করতে হবে।
            ]);

            return $user->load('teacherProfile');
        });
    }

    /**
     * সব শিক্ষকের লিস্ট
     */
    public function getAllTeachers()
    {
        return User::role('teacher')->with('teacherProfile')->paginate(10);
    }
}