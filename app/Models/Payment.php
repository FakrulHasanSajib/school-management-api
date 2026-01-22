<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * mass assignable ফিল্ডসমূহ।
     * ERD অনুযায়ী এই তথ্যগুলো পেমেন্ট ট্র্যাকিংয়ের জন্য প্রয়োজন।
     */
    protected $fillable = [
        'invoice_id',    // কোন ইনভয়েসের বিপরীতে পেমেন্ট হচ্ছে 
        'method',        // পেমেন্ট মাধ্যম (Bkash, Cash, Bank) 
        'amount',        // কত টাকা জমা দেওয়া হলো 
        'transaction_id',// ইউনিক ট্রানজেকশন আইডি 
        'paid_at'        // পেমেন্ট জমা দেওয়ার তারিখ 
    ];

    /**
     * একটি পেমেন্ট একটি নির্দিষ্ট ইনভয়েসের অধীনে থাকে।
     * RELATION: PAYMENTS ||--o{ FEE_INVOICES
     */
    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, 'invoice_id'); 
    }
}