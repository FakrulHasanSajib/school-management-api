<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model {
    use HasFactory;
    protected $table = 'staff'; // টেবিল নাম নির্দিষ্ট করে দিলাম
    protected $fillable = ['user_id', 'designation_id', 'joining_date', 'address'];
}