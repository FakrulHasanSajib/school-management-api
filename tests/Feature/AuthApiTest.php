<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

// টেস্ট শুরু করার আগে ডাটাবেস ফ্রেশ করা (যাতে আগের ডাটা ঝামেলা না করে)
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin can login with valid credentials', function () {
    // ১. পরিবেশ সেটআপ: রোল তৈরি করা
    Role::create(['name' => 'super-admin']);

    // ২. একজন ইউজার তৈরি করা
    $user = User::factory()->create([
        'email' => 'admin@school.com',
        'password' => bcrypt('password'), // পাসওয়ার্ড এনক্রিপ্ট করে রাখা
    ]);
    $user->assignRole('super-admin');

    // ৩. API তে রিকোয়েস্ট পাঠানো (Postman এর কাজ এখানে কোড দিয়ে হচ্ছে)
    $response = $this->postJson('/api/login', [
        'email' => 'admin@school.com',
        'password' => 'password',
    ]);

    // ৪. চেক করা (Assert)
    $response->assertStatus(200) // স্ট্যাটাস ২০০ হতে হবে
             ->assertJsonStructure([ // জেসন স্ট্রাকচার ঠিক আছে কিনা
                 'status',
                 'message',
                 'token',
                 'user'
             ]);
});

test('login fails with wrong password', function () {
    // ১. ইউজার তৈরি
    $user = User::factory()->create([
        'email' => 'test@school.com',
        'password' => bcrypt('password'),
    ]);

    // ২. ভুল পাসওয়ার্ড দিয়ে রিকোয়েস্ট পাঠানো
    $response = $this->postJson('/api/login', [
        'email' => 'test@school.com',
        'password' => 'wrong-password',
    ]);

    // ৩. চেক করা: এটা ফেইল হওয়ার কথা (401 Unauthorized)
    $response->assertStatus(401)
             ->assertJson([
                 'status' => false
             ]);
});