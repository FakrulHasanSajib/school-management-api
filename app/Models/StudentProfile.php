<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'class_id', 'section_id', 'parent_id', 
        'admission_no', 'roll_no', 'dob', 'gender', 'address'
    ]; 

    // রিলেশনশিপ
    public function user() { return $this->belongsTo(User::class); } 
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
}