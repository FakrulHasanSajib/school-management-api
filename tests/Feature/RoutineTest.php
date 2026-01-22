<?php

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherProfile;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin can create a routine', function () {
    // ১. সেটআপ
    Role::create(['name' => 'super-admin']);
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Sanctum::actingAs($admin, ['*']);

    // ২. প্রয়োজনীয় ডাটা তৈরি
    $class = SchoolClass::create(['name' => 'Class Ten', 'numeric_value' => 10]);
    $section = Section::create(['name' => 'Section A', 'class_id' => $class->id]);
    
    // 👇 পরিবর্তন: এখানে 'code' এবং 'type' যোগ করা হয়েছে
    $subject = Subject::create([
        'name' => 'Math', 
        'class_id' => $class->id,
        'code' => 'MATH-101', 
        'type' => 'Theory'
    ]);
    
    // টিচার প্রোফাইল তৈরি
    $teacher = TeacherProfile::create([
        'user_id' => User::factory()->create()->id, 
        'designation' => 'Lecturer', 
        'qualification' => 'M.Sc', 
        'phone' => '017000', 
        'joining_date' => '2022-01-01'
    ]);

    // ৩. রুটিন তৈরির রিকোয়েস্ট
    $response = $this->postJson('/api/routines', [
        'class_id' => $class->id,
        'section_id' => $section->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'day' => 'Sunday',
        'start_time' => '10:00',
        'end_time' => '11:00'
    ]);

    $response->assertStatus(201)
             ->assertJson(['message' => 'Routine created successfully']);
});

test('cannot assign teacher to overlapping time slots', function () {
    // ১. সেটআপ
    Role::create(['name' => 'super-admin']);
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Sanctum::actingAs($admin, ['*']);

    $class = SchoolClass::create(['name' => 'Class Ten', 'numeric_value' => 10]);
    $sectionA = Section::create(['name' => 'Section A', 'class_id' => $class->id]);
    $sectionB = Section::create(['name' => 'Section B', 'class_id' => $class->id]);
    
    // 👇 পরিবর্তন: এখানেও 'code' এবং 'type' যোগ করা হয়েছে
    $subject = Subject::create([
        'name' => 'Math', 
        'class_id' => $class->id,
        'code' => 'MATH-101',
        'type' => 'Theory'
    ]);
    
    $teacher = TeacherProfile::create([
        'user_id' => User::factory()->create()->id, 
        'designation' => 'Lecturer', 
        'qualification' => 'M.Sc', 
        'phone' => '017000', 
        'joining_date' => '2022-01-01'
    ]);

    // ২. ১ম রুটিন (সফল হবে)
    $this->postJson('/api/routines', [
        'class_id' => $class->id,
        'section_id' => $sectionA->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'day' => 'Sunday',
        'start_time' => '10:00',
        'end_time' => '11:00'
    ]);

    // ৩. ২য় রুটিন (কনফ্লিক্ট হবে)
    $response = $this->postJson('/api/routines', [
        'class_id' => $class->id,
        'section_id' => $sectionB->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id, 
        'day' => 'Sunday',
        'start_time' => '10:30', 
        'end_time' => '11:30'
    ]);

    // ৪. চেক করা
    $response->assertStatus(422)
             ->assertJson(['message' => 'Teacher is already booked at this time!']);
});