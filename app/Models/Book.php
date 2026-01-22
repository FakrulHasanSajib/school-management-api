<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * Mass assignable ফিল্ডসমূহ।
     * ERD অনুযায়ী বইয়ের টাইটেল, লেখক, কোড এবং সংখ্যা থাকবে। [cite: 14]
     */
    protected $fillable = [
        'title',
        'author',
        'isbn',      // বইয়ের ইউনিক আইডি বা কোড
        'quantity',  // লাইব্রেরিতে কয়টি কপি আছে
        'status',
        'category'     // Available or Not
    ];

    /**
     * একটি বই অনেকবার ইস্যু হতে পারে।
     * RELATION: BOOKS ||--o{ BOOK_ISSUES 
     */
    public function issues()
    {
        return $this->hasMany(BookIssue::class);
    }
}