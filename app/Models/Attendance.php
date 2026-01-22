<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // ❌ এই লাইনটি মুছে দিন বা কমেন্ট করুন
    // protected $table = 'student_attendances'; 

    // ✅ লারাভেল অটোমেটিক 'attendances' টেবিল খুঁজে নেবে
    protected $fillable = [
        'student_id', 
        'class_id', 
        'section_id', 
        'date', 
        'status',
        'remarks'
    ];

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}