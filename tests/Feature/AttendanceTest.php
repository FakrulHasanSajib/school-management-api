<?php

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentProfile;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('teacher can submit attendance', function () {
    // ১. সেটআপ
    Role::create(['name' => 'teacher']);
    Role::create(['name' => 'student']); // স্টুডেন্ট তৈরির জন্য রোল লাগবে

    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');
    Sanctum::actingAs($teacher, ['*']);

    $class = SchoolClass::create(['name' => 'Class Ten', 'numeric_value' => 10]);
    $section = Section::create(['name' => 'Section A', 'class_id' => $class->id]);

    // ২. স্টুডেন্ট তৈরি (ম্যানুয়ালি)
    $studentUser = User::factory()->create(['role' => 'student']);
    $studentProfile = StudentProfile::create([
        'user_id' => $studentUser->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'admission_no' => '1001',
        'roll_no' => '01',
        'gender' => 'Male',
        'dob' => '2010-01-01',
        'address' => 'Dhaka'
    ]);

    // ৩. হাজিরা সাবমিট করা
    $response = $this->postJson('/api/attendance', [
        'class_id' => $class->id,
        'section_id' => $section->id,
        'date' => '2026-01-22',
        'attendances' => [
            [
                'student_id' => $studentProfile->id,
                'status' => 'Present'
            ]
        ]
    ]);

    // ৪. চেক করা
    $response->assertStatus(201)
             ->assertJson(['message' => 'Attendance recorded successfully']);

    $this->assertDatabaseHas('attendances', [
        'student_id' => $studentProfile->id,
        'status' => 'Present',
        'date' => '2026-01-22'
    ]);
});