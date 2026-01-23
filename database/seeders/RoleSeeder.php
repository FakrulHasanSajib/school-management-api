<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Spatie Permission এর ক্যাশ ক্লিয়ার করা
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // রোলগুলোর তালিকা
        $roles = [
            'super-admin',
            'admin',
            'teacher',
            'student',
            'parent',
            'accountant',
        ];

        // লুপ চালিয়ে রোল তৈরি করা হচ্ছে
        foreach ($roles as $role) {
            // ১. Web গার্ডের জন্য (ব্লেড ফাইলের জন্য)
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            
            // ২. ✅ API গার্ডের জন্য (Vue/React বা Sanctum এর জন্য - এটা মাস্ট লাগবে)
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        }
    }
}