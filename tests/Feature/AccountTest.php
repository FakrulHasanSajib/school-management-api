<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\FeeType;
use App\Models\FeeInvoice;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_create_fee_invoice_for_student()
    {
        Role::findOrCreate('super-admin', 'web'); 
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin, ['*']);

        // ক্লাস এবং সেকশন তৈরি (Foreign Key এরর এড়াতে)
        $class = SchoolClass::create(['name' => 'Class 10', 'numeric_value' => 10]);
        $section = Section::create(['name' => 'A', 'class_id' => $class->id]);
        $studentUser = User::factory()->create(['role' => 'student']);
        
        $student = StudentProfile::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'admission_no' => 'ADM-001',
            'roll_no' => '1',
            'gender' => 'Male',
            'dob' => '2010-01-01',
            'address' => 'Dhaka'
        ]);

        $feeType = FeeType::create(['name' => 'Tuition Fee', 'amount' => 2000]);

        $response = $this->postJson('/api/accounts/invoices', [
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'due_date' => '2026-02-10'
        ]);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Invoice generated successfully']);
    }

    #[Test]
    public function student_can_pay_fee_invoice()
    {
        Role::findOrCreate('super-admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin, ['*']);

        // ক্লাস এবং সেকশন তৈরি (Foreign Key এরর এড়াতে)
        $class = SchoolClass::create(['name' => 'Class 10', 'numeric_value' => 10]);
        $section = Section::create(['name' => 'A', 'class_id' => $class->id]);
        $studentUser = User::factory()->create(['role' => 'student']);
        
        $student = StudentProfile::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'admission_no' => 'ADM-002',
            'roll_no' => '2',
            'gender' => 'Male',
            'dob' => '2010-01-01',
            'address' => 'Dhaka'
        ]);
        
        $feeType = FeeType::create(['name' => 'Exam Fee', 'amount' => 500]);
        
        $invoice = FeeInvoice::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'total_amount' => 500,
            'paid_amount' => 0,
            'due_amount' => 500,
            'due_date' => '2026-02-20',
            'status' => 'Pending'
        ]);

        $response = $this->postJson('/api/accounts/payments', [
            'fee_invoice_id' => $invoice->id,
            'amount' => 300,
            'payment_method' => 'bkash',
            'transaction_id' => 'TRX' . uniqid()
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('fee_invoices', [
            'id' => $invoice->id,
            'paid_amount' => 300,
            'due_amount' => 200,
            'status' => 'Partial'
        ]);
    }
}