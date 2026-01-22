<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    protected $fillable = ['name', 'amount', 'description'];

    public function invoices() {
        return $this->hasMany(FeeInvoice::class, 'fee_type_id'); 
    }
}