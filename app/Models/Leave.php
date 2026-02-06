<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = ['user_id', 'type', 'start_date', 'end_date', 'status','reason'];

    // ছুটিটি কোন ইউজারের (টিচার বা স্টাফ)
    public function user() {
        return $this->belongsTo(User::class);
    }
}
