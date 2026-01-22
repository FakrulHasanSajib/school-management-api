<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookIssue extends Model
{
    protected $fillable = [
        'book_id', 
    'student_id', 
    'issue_date', 
    'return_date', // ✅
    'returned_at',
    'status' // Issued, Returned, Overdue
    ];

    // বইয়ের সাথে রিলেশন
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // স্টুডেন্টের সাথে রিলেশন 
    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    // টিচারের সাথে রিলেশন 
    public function teacher()
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_id');
    }
}