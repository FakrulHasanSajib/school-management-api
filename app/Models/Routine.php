<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    protected $fillable = [
        'class_id', 
        'section_id', 
        'subject_id', 
        'teacher_id', 
        'day', 
        'start_time', 
        'end_time'
    ];

    // রিলেশনশিপসমূহ [cite: 9]
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(TeacherProfile::class, 'teacher_id'); }
}