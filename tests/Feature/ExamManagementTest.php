<?php

use App\Models\User;
use App\Models\Exam;
use App\Models\SchoolClass; // ✅ ক্লাস মডেল ইমপোর্ট
use App\Models\Subject;
use App\Models\StudentProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('admin can create an exam', function () {
    Role::findOrCreate('super-admin', 'web');
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Sanctum::actingAs($admin, ['*']);

    // ১. ক্লাস তৈরি করা জরুরি
    $class = SchoolClass::create(['name' => 'Class 10', 'numeric_value' => 10]);

    $response = $this->postJson('/api/exams', [
        'name' => 'Final Exam 2026',
        'class_id' => $class->id, // ✅ class_id যোগ করা হয়েছে
        'session' => '2026',
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-15'
    ]);

    $response->assertStatus(201)
             ->assertJson(['message' => 'Exam created successfully']);

    $this->assertDatabaseHas('exams', ['name' => 'Final Exam 2026']);
});

test('teacher can add marks for student', function () {
    Role::findOrCreate('teacher', 'web');
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');
    Sanctum::actingAs($teacher, ['*']);

    // ১. প্রয়োজনীয় ডাটা সেটআপ
    $class = SchoolClass::create(['name' => 'Class 10', 'numeric_value' => 10]);
    
    // ✅ মিসিং লাইন: সেকশন তৈরি করা হলো
    $section = \App\Models\Section::create(['name' => 'A', 'class_id' => $class->id]); 

    $exam = Exam::create([
        'name' => 'Mid Term',
        'session' => '2026', 
        'class_id' => $class->id, 
        'start_date' => now(), 
        'end_date' => now()
    ]);
    
    $subject = Subject::create(['name' => 'Math', 'code' => '101', 'class_id' => $class->id]);
    
    $studentUser = User::factory()->create();
    $student = StudentProfile::create([
        'user_id' => $studentUser->id,
        'class_id' => $class->id,
        'section_id' => $section->id, // ✅ এখন আর এরর দিবে না কারণ $section উপরে তৈরি করা হয়েছে
        'admission_no' => '1001',
        'roll_no' => '01',
        'gender' => 'Male',
        'dob' => '2010-01-01',
        'address' => 'Dhaka',
        'session' => '2026',
    ]);

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