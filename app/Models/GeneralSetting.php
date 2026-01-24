<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name', 'school_address', 'phone', 'email', 'school_logo', 'principal_signature'
    ];

    // ফ্রন্টএন্ডে যাতে লোগোর পুরো URL পাওয়া যায়
    protected $appends = ['logo_url', 'signature_url'];

    public function getLogoUrlAttribute()
    {
        return $this->school_logo ? asset($this->school_logo) : null;
    }

    public function getSignatureUrlAttribute()
    {
        return $this->principal_signature ? asset($this->principal_signature) : null;
    }
}