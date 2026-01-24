<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; //
use App\Models\SchoolClass; //
use App\Models\Section; // ✅ এটি যোগ করুন
use App\Models\Attendance; // ✅ এটি যোগ করুন

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'class_id', 'section_id', 'parent_id', 
        'admission_no', 'roll_no', 'dob', 'gender', 'address', 'phone', 'blood_group', 'image'
    ];

    // --- রিলেশনশিপসমূহ ---

    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }

    public function schoolClass() 
    { 
        return $this->belongsTo(SchoolClass::class, 'class_id'); 
    }

    public function section() 
    { 
        return $this->belongsTo(Section::class); 
    }

    /**
     * স্টুডেন্টের সব হাজিরার ডাটা পাওয়ার জন্য
     */
    public function attendances()
    {
        // একজন স্টুডেন্টের অনেকগুলো হাজিরা থাকতে পারে (One-to-Many)
        return $this->hasMany(Attendance::class, 'student_id');
    }
}