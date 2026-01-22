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
        // Spatie Permission এর ক্যাশ ক্লিয়ার করা (Best Practice)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // রোলগুলোর তালিকা
        $roles = [
            'super-admin',
            'admin',
            'teacher',
            'student',
            'parent',
            'accountant', // ভবিষ্যতে লাগতে পারে তাই যুক্ত করলাম
        ];

        // লুপ চালিয়ে রোল তৈরি করা হচ্ছে
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}