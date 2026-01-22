<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\SchoolClass;
use App\Models\Payment;
use App\Models\FeeInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB; // ✅ DB ইম্পোর্ট

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_dashboard_stats()
    {
        // ১. অ্যাডমিন সেটআপ
        Role::findOrCreate('super-admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin, ['*']);

        // ২. ১০ জন স্টুডেন্ট তৈরি
        $class = SchoolClass::create(['name' => 'Ten', 'numeric_value' => 10]);
        $section = \App\Models\Section::create(['name' => 'A', 'class_id' => $class->id]);
        
        $users = User::factory()->count(10)->create();
        foreach($users as $user) {
            StudentProfile::create([
                'user_id' => $user->id,
                'class_id' => $class->id,
                'section_id' => $section->id,
                'admission_no' => 'ADM-' . $user->id,
                'roll_no' => $user->id,
                'gender' => 'Male',
                'dob' => '2010-01-01',
                'address' => 'Dhaka'
            ]);
        }

        // ৩. ৫ জন টিচার তৈরি
        $teachers = User::factory()->count(5)->create();
        foreach($teachers as $teacher) {
            TeacherProfile::create([
                'user_id' => $teacher->id,
                'designation' => 'Lecturer',
                'phone' => '01700000000',
                'gender' => 'Female',
                'joining_date' => '2020-01-01',
                'address' => 'Dhaka',
                'salary' => 25000,
                'qualification' => 'M.Sc in Math'
            ]);
        }

        // ৪. পেমেন্ট তৈরির আগে FeeType এবং Invoice তৈরি
        // ফেইক FeeType তৈরি (সরাসরি DB ব্যবহার করে, যাতে মডেল না থাকলেও সমস্যা না হয়)
        $feeTypeId = DB::table('fee_types')->insertGetId([
            'name' => 'Monthly Fee',
            'amount' => 2000,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $student1 = StudentProfile::first();
        $student2 = StudentProfile::skip(1)->first();

        // Invoice 1 (স্কিমা অনুযায়ী ফিল্ড ফিক্সড)
        $invoice1 = FeeInvoice::create([
            'student_id' => $student1->id,
            // 'class_id' => $class->id, // ❌ বাদ দেওয়া হয়েছে (স্কিমাতে নেই)
            'fee_type_id' => $feeTypeId, // ✅ type -> fee_type_id
            'total_amount' => 2000,      // ✅ amount -> total_amount
            'due_amount' => 0,           // ✅ due_amount যোগ করা হয়েছে (পেইড হবে তাই ০)
            'paid_amount' => 2000,       // ✅ paid_amount
            'status' => 'Paid',
            'due_date' => now()
        ]);

        // Invoice 2
        $invoice2 = FeeInvoice::create([
            'student_id' => $student2->id,
            'fee_type_id' => $feeTypeId,
            'total_amount' => 3000,
            'due_amount' => 0,
            'paid_amount' => 3000,
            'status' => 'Paid',
            'due_date' => now()
        ]);

        // এবার পেমেন্ট তৈরি
        Payment::create([
            'student_id' => $student1->id, 
            'invoice_id' => $invoice1->id,
            'amount' => 2000, 
            'transaction_id' => 'TXN1', 
            'method' => 'Cash', 
            'paid_at' => now()
        ]);
        
        Payment::create([
            'student_id' => $student2->id, 
            'invoice_id' => $invoice2->id, 
            'amount' => 3000, 
            'transaction_id' => 'TXN2', 
            'method' => 'Bkash', 
            'paid_at' => now()
        ]);

        // ৫. ড্যাশবোর্ড এপিআই কল
        $response = $this->getJson('/api/dashboard/stats');

        // ৬. চেক করা
        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'total_students' => 10,
                         'total_teachers' => 5,
                         'total_income' => 5000
                     ]
                 ]);
    }
}