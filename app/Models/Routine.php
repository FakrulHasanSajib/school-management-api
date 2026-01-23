<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    protected $fillable = [
        'class_id', 'section_id', 'subject_id', 'teacher_id', 
        'day', 'start_time', 'end_time'
    ];

    // ✅ ফাংশনের নাম 'schoolClass' (কারণ ফাইলের নাম SchoolClass.php)
    public function schoolClass() { 
        return $this->belongsTo(SchoolClass::class, 'class_id'); 
    }

    public function section() { 
        return $this->belongsTo(Section::class); 
    }

    public function subject() { 
        return $this->belongsTo(Subject::class); 
    }

    // ✅ টিচার ইউজার টেবিলের সাথে যুক্ত
    public function teacher() { 
        return $this->belongsTo(User::class, 'teacher_id'); 
    }
}