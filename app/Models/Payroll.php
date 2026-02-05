<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model {
    use HasFactory;

    // ✅ ফ্রন্টএন্ড থেকে আসা সব ফিল্ড এখানে অ্যালাউ করা হলো
    protected $fillable = [
        'user_id',       // staff_id এর বদলে user_id (কারণ টিচাররা User টেবিলে থাকে)
        'salary_month',  // month & year এর বদলে সরাসরি salary_month (যেমন: "2026-02")
        'amount',        // বেসিক স্যালারি
        'bonus',
        'deduction',
        'net_salary',    // সব যোগ-বিয়োগ করে ফাইনাল এমাউন্ট
        'payment_date',
        'status',
        'note'
    ];

    // ইউজার রিলেশন (যাতে কার স্যালারি তা বোঝা যায়)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
