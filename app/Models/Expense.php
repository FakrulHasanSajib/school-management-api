<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_category_id', 'amount', 'expense_date', 'description'
    ];

    // ✅ এই ফাংশনটি মিসিং ছিল, তাই এরর আসছিল
    public function category()
    {
        // ExpenseCategory মডেলের সাথে সম্পর্ক জুড়ে দেওয়া হলো
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }
}
