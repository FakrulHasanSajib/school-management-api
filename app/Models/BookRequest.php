<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'book_id', 'request_date', 'status'];

    // বইয়ের সাথে সম্পর্ক
    public function book() {
        return $this->belongsTo(Book::class);
    }

    // ইউজারের সাথে সম্পর্ক
    public function user() {
        return $this->belongsTo(User::class);
    }
}
