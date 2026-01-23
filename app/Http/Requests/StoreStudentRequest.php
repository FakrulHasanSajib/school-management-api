<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // ✅ রুল ক্লাসের জন্য
use App\Models\StudentProfile; // ✅ স্টুডেন্ট মডেল ইমপোর্ট

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // ১. ইমেইল ভ্যালিডেশন লজিক (যাতে আপডেটের সময় নিজের ইমেইল নিয়ে সমস্যা না করে)
        $emailRule = 'required|email|unique:users,email';

        // যদি এটি আপডেট (PUT/PATCH) রিকোয়েস্ট হয়
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $studentId = $this->route('id'); // URL থেকে স্টুডেন্ট আইডি নেওয়া
            $student = StudentProfile::find($studentId);
            
            if ($student) {
                // ইউজার টেবিলে চেক করার সময় ঐ স্টুডেন্টের 'user_id' কে ইগনোর করতে হবে
                $emailRule = [
                    'required', 
                    'email', 
                    Rule::unique('users', 'email')->ignore($student->user_id)
                ];
            }
        }

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRule, // ✅ ফিক্স করা ইমেইল রুল
            'password' => $this->isMethod('post') ? 'required|min:6' : 'nullable|min:6',
            
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            
            // admission_no এর ক্ষেত্রে route('id') ঠিক আছে কারণ এটা student_profiles টেবিল
            'admission_no' => 'required|string|unique:student_profiles,admission_no,' . $this->route('id'),
            
            'roll_no' => 'required', // String/Number দুটোই সাপোর্ট করবে
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required|date',

            // 👇 আপনার রিকোয়ারমেন্ট অনুযায়ী address এখন required
            'address' => 'required|string|max:500', 
        ];
    }
}