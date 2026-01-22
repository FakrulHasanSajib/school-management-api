<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
   protected $fillable = ['name', 'session', 'start_date', 'end_date', 'is_active']; // ✅ session থাকতে হবে

    // একটি পরীক্ষার অধীনে অনেকগুলো বিষয়ের শিডিউল থাকতে পারে
    public function schedules()
    {
        return $this->hasMany(ExamSchedule::class); 
    }

    // একটি পরীক্ষার অনেকগুলো মার্কস রেকর্ড থাকতে পারে
    public function marks()
    {
        return $this->hasMany(ExamMark::class);
    }
}