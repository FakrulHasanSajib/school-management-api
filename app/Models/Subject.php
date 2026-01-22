<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    // ERD অনুযায়ী ফিল্ডগুলো 
    protected $fillable = [
        'class_id', 
        'name',     // Mathematics
        'code',     // MATH101
        'type'      // Theory/Practical
    ];

    // ক্লাসের সাথে সম্পর্ক
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}