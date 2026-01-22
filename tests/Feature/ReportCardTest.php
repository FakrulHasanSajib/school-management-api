<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\ExamMark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

class ReportCardTest extends TestCase
{
    use RefreshDatabase;

   #[Test]
public function student_can_view_report_card_with_gpa()
{
    // ১. রোল এবং ইউজার সেটআপ
    Role::findOrCreate('student', 'web');
    $studentUser = User::factory()->create();
    $studentUser->assignRole('student');
    Sanctum::actingAs($studentUser, ['*']);

    // ২. ক্লাস ও সেকশন তৈরি (ফিক্সড)
    $class = SchoolClass::create(['name' => 'Class 10', 'numeric_value' => 10]);
    $section = \App\Models\Section::create(['name' => 'A', 'class_id' => $class->id]); // ✅ সেকশন তৈরি

    // ৩. স্টুডেন্ট তৈরি (section_id সহ)
    $student = StudentProfile::create([
        'user_id' => $studentUser->id,
        'class_id' => $class->id,
        'section_id' => $section->id, // ✅ এখন আর এরর দিবে না
        'admission_no' => 'RC-100',
        'roll_no' => '01',
        'gender' => 'Male',
        'dob' => '2010-01-01'
    ]);

    $exam = Exam::create(['name' => 'Final Term', 'class_id' => $class->id, 'start_date' => now(), 'end_date' => now()]);

    // ৪. মার্কস এন্ট্রি (Bangla: 80 [GPA 5.0], English: 50 [GPA 3.0])
    $bangla = Subject::create(['name' => 'Bangla', 'code' => '101', 'class_id' => $class->id, 'total_marks' => 100, 'pass_marks' => 33]);
    $english = Subject::create(['name' => 'English', 'code' => '102', 'class_id' => $class->id, 'total_marks' => 100, 'pass_marks' => 33]);

    ExamMark::create(['exam_id' => $exam->id, 'student_id' => $student->id, 'subject_id' => $bangla->id, 'marks_obtained' => 80]);
    ExamMark::create(['exam_id' => $exam->id, 'student_id' => $student->id, 'subject_id' => $english->id, 'marks_obtained' => 50]);

    // ৫. রিপোর্ট কার্ড দেখার রিকোয়েস্ট
    $response = $this->getJson("/api/exams/{$exam->id}/report-card/{$student->id}");

    // ৬. চেক করা
    $response->assertStatus(200)
                ->assertJsonStructure([
                    'student_name',
                    'final_gpa'
                ]);
    
    // GPA ৪.০০ এসেছে কি না চেক করা (5.0 + 3.0) / 2 = 4.0
    $this->assertEquals(4.00, $response->json('final_gpa'));
}
}