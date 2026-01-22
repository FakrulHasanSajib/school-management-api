<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
protected $table = 'classes'; // টেবিল নাম বলে দিলাম
    protected $fillable = ['name', 'numeric_value'];

    public function sections() {
        return $this->hasMany(Section::class, 'class_id');
    }
}