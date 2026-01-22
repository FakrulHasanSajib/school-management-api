<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    protected $fillable = [
        'exam_id', 
        'subject_id', 
        'exam_date', 
        'start_time', 
        'end_time', 
        'room_no'
    ];

    // এটি নির্দিষ্ট একটি পরীক্ষার অংশ
    public function exam()
    {
        return $this->belongsTo(Exam::class); 
    }

    // এটি নির্দিষ্ট একটি বিষয়ের জন্য
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}