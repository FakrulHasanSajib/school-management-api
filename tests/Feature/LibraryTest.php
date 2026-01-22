<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\StudentProfile;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

class LibraryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_add_a_new_book()
    {
        Role::findOrCreate('super-admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson('/api/library/books', [
            'title' => 'Laravel Deep Dive',
            'author' => 'Taylor Otwell',
            'isbn' => '123-456789',
            'quantity' => 5,
            'category' => 'Technology'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', ['title' => 'Laravel Deep Dive']);
    }

    #[Test]
    public function librarian_can_issue_book_to_student()
    {
        // ১. রোল এবং লাইব্রেরিয়ান সেটআপ
        Role::findOrCreate('librarian', 'web');
        $librarian = User::factory()->create();
        $librarian->assignRole('librarian');
        Sanctum::actingAs($librarian, ['*']);

        // ২. রিলেশনশিপ এরর এড়াতে ক্লাস ও সেকশন তৈরি করা
        $class = SchoolClass::create(['name' => 'Class 10', 'numeric_value' => 10]);
        $section = Section::create(['name' => 'A', 'class_id' => $class->id]);

        // ৩. বই তৈরি করা
        $book = Book::create([
            'title' => 'PHP Mastery', 
            'author' => 'Zeev', 
            'isbn' => '999-888', 
            'quantity' => 10
        ]);

        // ৪. স্টুডেন্ট তৈরি করা (সঠিক ইউজার এবং ক্লাস আইডি সহ)
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = StudentProfile::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'admission_no' => 'LIB-ADM-' . uniqid(),
            'roll_no' => '101',
            'gender' => 'Male',
            'dob' => '2010-01-01',
            'address' => 'Dhaka'
        ]);

        // ৫. বই ইস্যু করার রিকোয়েস্ট (সার্ভিসের return_date অনুযায়ী)
        $response = $this->postJson('/api/library/issue', [
            'book_id' => $book->id,
            'student_id' => $student->id,
            'return_date' => now()->addDays(7)->toDateString()
        ]);

        // ৬. চেক করা
        $response->assertStatus(201);
        $this->assertDatabaseHas('book_issues', [
            'book_id' => $book->id,
            'student_id' => $student->id,
            'status' => 'Issued'
        ]);
    }
}