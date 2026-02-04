<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    // ✅ due_date যোগ করা হয়েছে
    protected $fillable = ['name', 'amount', 'description', 'due_date'];

    public function invoices() {
        return $this->hasMany(FeeInvoice::class, 'fee_type_id');
    }
}
