<?php

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin can admit a new student', function () {
    // ১. সেটআপ
    Role::create(['name' => 'super-admin']);
    Role::create(['name' => 'student']); // স্টুডেন্ট রোল থাকতে হবে
    
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Sanctum::actingAs($admin, ['*']);

    // ২. ক্লাস ও সেকশন তৈরি (ভর্তির জন্য জরুরি)
    $class = SchoolClass::create(['name' => 'Class Six', 'numeric_value' => 6]);
    $section = Section::create(['name' => 'Section A', 'class_id' => $class->id]);

    // ৩. ভর্তির রিকোয়েস্ট পাঠানো
    $response = $this->postJson('/api/students/admit', [
        'name' => 'Rahim Uddin',
        'email' => 'rahim@student.com',
        'password' => '123456',
        'class_id' => $class->id,
        'section_id' => $section->id,
        'admission_no' => 'ADM-001',
        'roll_no' => '01',
        'gender' => 'Male',
        'dob' => '2010-01-01',
        'address' => 'Dhaka, Bangladesh'
    ]);

    // ৪. চেক করা
    $response->assertStatus(201)
             ->assertJson(['message' => 'Student admitted successfully']);

    // ডাটাবেসে চেক করা
    $this->assertDatabaseHas('users', ['email' => 'rahim@student.com']);
    $this->assertDatabaseHas('student_profiles', ['admission_no' => 'ADM-001']);
});

test('cannot admit student with duplicate email', function () {
    // সেটআপ (Role, User, Class, Section তৈরি)...
    Role::create(['name' => 'super-admin']);
    Role::create(['name' => 'student']);
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Sanctum::actingAs($admin, ['*']);

    $class = SchoolClass::create(['name' => 'Class Six', 'numeric_value' => 6]);
    $section = Section::create(['name' => 'Section A', 'class_id' => $class->id]);

    // ১. প্রথম স্টুডেন্ট ভর্তি (সফল হবে)
    $this->postJson('/api/students/admit', [
        'name' => 'Student 1',
        'email' => 'duplicate@school.com',
        'password' => 'password',
        'class_id' => $class->id,
        'section_id' => $section->id,
        'admission_no' => 'ADM-100',
        'roll_no' => '01',
        'gender' => 'Male',
        'dob' => '2010-01-01',
        'address' => 'Dhaka, Bangladesh' // ✅ অবশ্যই দিতে হবে
    ]);

    // ২. দ্বিতীয় স্টুডেন্ট ভর্তি চেষ্টা (একই ইমেইল দিয়ে)
    $response = $this->postJson('/api/students/admit', [
        'name' => 'Student 2',
        'email' => 'duplicate@school.com', // ডুপ্লিকেট ইমেইল
        'password' => 'password',
        'class_id' => $class->id,
        'section_id' => $section->id,
        'admission_no' => 'ADM-101', // ভিন্ন রোল
        'roll_no' => '02',
        'gender' => 'Male',
        'dob' => '2010-01-01',
        'address' => 'Chittagong, Bangladesh' // ✅ এখানেও দিতে হবে
    ]);

    // ৩. চেক করা (Validation Error আশা করছি)
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});