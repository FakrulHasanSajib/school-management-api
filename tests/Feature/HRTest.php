<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Designation;
use App\Models\Staff; // স্টাফ মডেল লাগবে
use App\Models\Payroll; // পেরোল মডেল লাগবে
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

class HRTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_create_designation()
    {
        Role::findOrCreate('super-admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson('/api/hr/designations', [
            'name' => 'Accountant',
            'basic_salary' => 15000
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('designations', ['name' => 'Accountant']);
    }

    #[Test]
    public function admin_can_pay_staff_salary()
    {
        Role::findOrCreate('super-admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin, ['*']);

        // ১. ডেজিগনেশন তৈরি
        $designation = Designation::create(['name' => 'Driver', 'basic_salary' => 10000]);

        // ২. স্টাফ তৈরি (User সহ)
        $staffUser = User::factory()->create(['role' => 'staff']);
        $staff = Staff::create([
            'user_id' => $staffUser->id,
            'designation_id' => $designation->id,
            'joining_date' => '2024-01-01',
            'address' => 'Dhaka'
        ]);

        // ৩. স্যালারি পেমেন্ট রিকোয়েস্ট
        $response = $this->postJson('/api/hr/payroll/pay', [
            'staff_id' => $staff->id,
            'month' => 'January',
            'year' => 2026,
            'amount' => 10000,
            'status' => 'Paid'
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('payrolls', [
            'staff_id' => $staff->id,
            'month' => 'January',
            'amount' => 10000
        ]);
    }
}