<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin can create a new teacher', function () {
    // ১. সেটআপ
    Role::create(['name' => 'super-admin']);
    Role::create(['name' => 'teacher']);
    
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Sanctum::actingAs($admin, ['*']);

    // ২. রিকোয়েস্ট পাঠানো
    $response = $this->postJson('/api/teachers', [
        'name' => 'Abul Kashem',
        'email' => 'kashem@school.com',
        'password' => 'password',
        'designation' => 'Senior Teacher',
        'qualification' => 'M.Sc in Physics',
        'phone' => '01700000000',
        'joining_date' => '2020-01-01',
        'gender' => 'Male'
    ]);

    // ৩. চেক করা
    $response->assertStatus(201)
             ->assertJson(['message' => 'Teacher created successfully']);

    // ডাটাবেস চেক
    $this->assertDatabaseHas('users', ['email' => 'kashem@school.com', 'role' => 'teacher']);
    $this->assertDatabaseHas('teacher_profiles', ['designation' => 'Senior Teacher']);
});

test('cannot create teacher with duplicate email', function () {
    Role::create(['name' => 'super-admin']);
    Role::create(['name' => 'teacher']);
    
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Sanctum::actingAs($admin, ['*']);

    // প্রথম টিচার
    $this->postJson('/api/teachers', [
        'name' => 'Teacher 1',
        'email' => 'teacher@school.com',
        'password' => 'password',
        'designation' => 'Teacher',
        'qualification' => 'B.Ed',
        'phone' => '01711111111',
        'joining_date' => '2022-01-01',
        'gender' => 'Male'
    ]);

    // দ্বিতীয় টিচার (একই ইমেইল)
    $response = $this->postJson('/api/teachers', [
        'name' => 'Teacher 2',
        'email' => 'teacher@school.com', // Duplicate
        'password' => 'password',
        'designation' => 'Teacher',
        'qualification' => 'B.Ed',
        'phone' => '01722222222',
        'joining_date' => '2022-01-01',
        'gender' => 'Female'
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});