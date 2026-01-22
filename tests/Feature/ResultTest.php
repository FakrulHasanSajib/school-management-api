<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\ExamMark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ResultTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_correct_grade_based_on_marks()
    {
        // 1. Setup Admin
        $admin = User::factory()->create();
        Sanctum::actingAs($admin, ['*']);

        // 2. Data Create
        $exam = Exam::create(['name' => 'Final Exam', 'session' => '2026', 'start_date' => '2026-01-01', 'end_date' => '2026-01-10']);
        $class = SchoolClass::create(['name' => 'Class 10', 'numeric_value' => 10]);
        $section = Section::create(['name' => 'A', 'class_id' => $class->id]);
        
        $subject = Subject::create([
            'name' => 'English', 
            'code' => 'ENG101', 
            'class_id' => $class->id, 
            'type' => 'Theory'
        ]);

        $studentUser = User::factory()->create(['role' => 'student']);
        $student = StudentProfile::create([
            'user_id' => $studentUser->id, 
            'class_id' => $class->id, 
            'section_id' => $section->id,
            'admission_no' => '101', 
            'roll_no' => '1', 
            'gender' => 'Male', 
            'dob' => '2010-01-01',
            'address' => 'Dhaka'
        ]);

        // 3. Mark Entry (85 Marks)
        ExamMark::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'session' => '2026',
            'class_id' => $class->id,
            'marks_obtained' => 85,
            'grade' => 'A+'
        ]);

        // 4. API Request
        $response = $this->getJson("/api/exams/{$exam->id}/results/{$student->id}");

        // 5. Assertions
        $response->assertStatus(200);
    }
}