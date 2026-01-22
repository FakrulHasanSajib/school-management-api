<?php

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\StudentProfile;
use App\Models\Exam;
use App\Models\ExamMark; // ✅ আপনার মডেল ব্যবহার করা হলো
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\Sanctum;
use App\Models\Section; // ✅ এই লাইনটি যোগ করুন

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin can create an exam', function () {
    // ১. সেটআপ
    Role::create(['name' => 'super-admin']);
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Sanctum::actingAs($admin, ['*']);

    // ২. রিকোয়েস্ট
    $response = $this->postJson('/api/exams', [
        'name' => 'Final Exam 2026',
        'session' => '2026',
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-15'
    ]);

    // ৩. চেক
    $response->assertStatus(201)
             ->assertJson(['message' => 'Exam created successfully']);

    $this->assertDatabaseHas('exams', ['name' => 'Final Exam 2026']);
});

test('teacher can add marks for student', function () {
    // ১. সেটআপ
    Role::create(['name' => 'teacher']);
    Role::create(['name' => 'student']);

    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');
    Sanctum::actingAs($teacher, ['*']);

    // ২. ডাটা তৈরি
    $exam = Exam::create(['name' => 'Mid Term', 'session' => '2026', 'start_date' => '2026-06-01', 'end_date' => '2026-06-10']);
    $class = SchoolClass::create(['name' => 'Ten', 'numeric_value' => 10]);
    
    // 👇 এটি যোগ করুন (এটি মিসিং ছিল)
    $section = Section::create(['name' => 'A', 'class_id' => $class->id]);
    
    $subject = Subject::create([
        'name' => 'Math', 
        'code' => 'MATH101', 
        'type' => 'Theory', 
        'class_id' => $class->id
    ]);
    
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = StudentProfile::create([
        'user_id' => $studentUser->id,
        'class_id' => $class->id,
        'section_id' => $section->id, // ✅ এখন ভেরিয়েবলটি পাওয়া যাবে
        'admission_no' => '999',
        'roll_no' => '01',
        'gender' => 'Male',
        'dob' => '2010-01-01',
        'address' => 'Dhaka'
    ]);

    // ৩. রিকোয়েস্ট (নিশ্চিত করুন আপনার Route এবং Controller আছে)
    $response = $this->postJson('/api/marks', [
        'exam_id' => $exam->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'marks' => [
            ['student_id' => $student->id, 'marks_obtained' => 85]
        ]
    ]);

    $response->assertStatus(201);
});