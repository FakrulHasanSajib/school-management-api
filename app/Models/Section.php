<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['class_id', 'name', 'capacity'];

    public function schoolClass() {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
