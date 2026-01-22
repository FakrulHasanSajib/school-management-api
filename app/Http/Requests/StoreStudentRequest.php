<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // সবাই এক্সেস পাবে (বা রোল চেক করতে পারেন)
    }

   public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $this->route('id'),
        'password' => $this->isMethod('post') ? 'required|min:6' : 'nullable',
        'class_id' => 'required|exists:classes,id',
        'section_id' => 'required|exists:sections,id',
        'admission_no' => 'required|string|unique:student_profiles,admission_no,' . $this->route('id'),
        'roll_no' => 'required|string',
        'gender' => 'required|in:Male,Female,Other',
        'dob' => 'required|date',
        
        // 👇 পরিবর্তন: nullable বাদ দিয়ে required করে দিন
        'address' => 'required|string|max:500', 
    ];
}
}