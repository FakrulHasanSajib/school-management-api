<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6', // কনফার্ম পাসওয়ার্ড চাইলে 'confirmed' যোগ করতে পারেন
            'designation' => 'required|string',
            'qualification' => 'required|string',
            'phone' => 'required|string',
            'joining_date' => 'required|date',
            // ✅ এই দুটি মিসিং ছিল, তাই ইমেজ যাচ্ছিল না
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'blood_group' => 'nullable|string',
            'address' => 'nullable|string' // যদি এড্রেস থাকে
        ];
    }
}
