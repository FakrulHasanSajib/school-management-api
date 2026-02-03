<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 1. এটি যুক্ত করুন (API এর জন্য)
use Spatie\Permission\Traits\HasRoles; // 2. এটি যুক্ত করুন (Role এর জন্য)

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // 3. এখানে নামগুলো ব্যবহার করুন
    protected $guard_name = 'api';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Role ফিল্ডটি fillable এ রাখা ভালো
        'status',
        'must_change_password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    // রিলেশনশিপ
    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }
}
