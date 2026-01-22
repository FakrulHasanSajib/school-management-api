<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    protected $table = 'classes'; // টেবিল নাম নির্দিষ্ট করা

    protected $fillable = ['name', 'numeric_value']; 

    // একটি ক্লাসের অনেক সেকশন থাকে
    public function sections() {
        return $this->hasMany(Section::class, 'class_id');
    }

    // একটি ক্লাসে অনেক সাবজেক্ট থাকে
    public function subjects() {
        return $this->hasMany(Subject::class, 'class_id');
    }
}