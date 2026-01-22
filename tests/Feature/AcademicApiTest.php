<?php

use App\Models\User;
use App\Models\SchoolClass;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\Sanctum;

// ডাটাবেস ফ্রেশ রাখা (যাতে আগের ডাটা ঝামেলা না করে)
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// --- ১. Class তৈরির টেস্ট ---

test('admin can create a new class', function () {
    Role::create(['name' => 'super-admin']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/academic/classes', [
        'name' => 'Class Ten',
        'numeric_value' => 10
    ]);

    $response->assertStatus(201)
             ->assertJson([
                 'status' => true,
                 'message' => 'Class created successfully'
             ]);

    $this->assertDatabaseHas('classes', ['name' => 'Class Ten']);
});

test('unauthorized user cannot create class', function () {
    $response = $this->postJson('/api/academic/classes', [
        'name' => 'Class Nine',
        'numeric_value' => 9
    ]);

    $response->assertStatus(401);
});


// --- ২. Section তৈরির টেস্ট (NEW) ---

test('admin can create a section for a class', function () {
    // সেটআপ
    Role::create(['name' => 'super-admin']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    Sanctum::actingAs($user, ['*']);

    // আগে একটি ক্লাস বানাতে হবে (কারণ সেকশন ক্লাসের আন্ডারে থাকে)
    $class = SchoolClass::create(['name' => 'Class Nine', 'numeric_value' => 9]);

    // সেকশন তৈরির রিকোয়েস্ট
    $response = $this->postJson('/api/academic/sections', [
        'class_id' => $class->id,
        'name' => 'Section A',
        'capacity' => 60
    ]);

    // চেক করা
    $response->assertStatus(201)
         ->assertJson(['message' => 'Section added successfully']);

    $this->assertDatabaseHas('sections', [
        'name' => 'Section A', 
        'class_id' => $class->id
    ]);
});


// --- ৩. Subject তৈরির টেস্ট (NEW) ---

test('admin can create a subject for a class', function () {
    // সেটআপ
    Role::create(['name' => 'super-admin']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    Sanctum::actingAs($user, ['*']);

    $class = SchoolClass::create(['name' => 'Class Ten', 'numeric_value' => 10]);

    // সাবজেক্ট তৈরির রিকোয়েস্ট
    $response = $this->postJson('/api/academic/subjects', [
        'class_id' => $class->id,
        'name' => 'Mathematics',
        'code' => 'MATH101',
        'type' => 'Theory'
    ]);

    // চেক করা
    $response->assertStatus(201)
             ->assertJson(['message' => 'Subject created successfully']);

    $this->assertDatabaseHas('subjects', ['name' => 'Mathematics']);
});

// --- ৪. ভ্যালিডেশন টেস্ট (NEW) ---

test('cannot create section without valid class_id', function () {
    Role::create(['name' => 'super-admin']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    Sanctum::actingAs($user, ['*']);

    // ভুল class_id (999) দিয়ে রিকোয়েস্ট পাঠানো
    $response = $this->postJson('/api/academic/sections', [
        'class_id' => 999, 
        'name' => 'Section A'
    ]);

    // 422 (Unprocessable Entity/Validation Error) আশা করছি
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['class_id']);
});