<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeInvoice extends Model
{
    protected $fillable = ['student_id', 'fee_type_id','total_amount','due_amount','paid_amount', 'title', 'amount', 'due_date', 'status']; 

    public function student() {
        return $this->belongsTo(StudentProfile::class, 'student_id'); 
    }

    public function feeType() {
        return $this->belongsTo(FeeType::class, 'fee_type_id'); 
    }

    public function payments() {
        return $this->hasMany(Payment::class, 'invoice_id'); 
    }
}