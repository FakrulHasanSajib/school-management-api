<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    protected $fillable = [
        'user_id', 
        'designation',   // e.g., Senior Teacher
        'qualification', // e.g., M.Sc in Math
        'phone', 
        'joining_date',
        'blood_group', 'image'
    ];

    // ইউজারের সাথে সম্পর্ক
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}