<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

class NoticeBoardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_publish_a_notice()
    {
        // ১. অ্যাডমিন সেটআপ
        Role::findOrCreate('super-admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin, ['*']);

        // ২. নোটিশ তৈরির রিকোয়েস্ট
        $response = $this->postJson('/api/notices', [
            'title' => 'Eidul Fitr Holiday',
            'content' => 'The school will remain closed for 5 days.',
            'published_at' => now()->toDateString(),
            'recipient_type' => 'all' // all, student, teacher
        ]);

        // ৩. চেক করা
        $response->assertStatus(201);
        $this->assertDatabaseHas('notices', [
            'title' => 'Eidul Fitr Holiday',
            'recipient_type' => 'all'
        ]);
    }

    #[Test]
    public function student_can_view_notices()
    {
        // ১. রোল এবং স্টুডেন্ট সেটআপ
        Role::findOrCreate('student', 'web');
        $student = User::factory()->create();
        $student->assignRole('student');
        Sanctum::actingAs($student, ['*']);

        // ২. ডাটাবেসে একটি নোটিশ থাকা দরকার
        Notice::create([
            'title' => 'Exam Schedule',
            'content' => 'Final exams start next week.',
            'published_at' => now()->toDateString(),
            'recipient_type' => 'all'
        ]);

        // ৩. নোটিশ দেখার রিকোয়েস্ট
        $response = $this->getJson('/api/notices');

        // ৪. চেক করা
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Exam Schedule']);
    }
}