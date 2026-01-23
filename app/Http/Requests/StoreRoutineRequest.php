<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoutineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ✅ আপনার টেবিল লিস্ট অনুযায়ী নাম ঠিক করা হলো
            'class_id' => 'required|exists:classes,id', 
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            
            // ✅ টিচার চেক হবে 'users' টেবিলে
            'teacher_id' => 'required|exists:users,id', 

            'day' => 'required|in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ];
    }
}