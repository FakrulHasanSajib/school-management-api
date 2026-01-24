<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    return [
        'class_id' => 'required|exists:classes,id',
        'section_id' => 'required|exists:sections,id',
        'date' => 'required|date',
        'attendances' => 'required|array', 
        'attendances.*.student_id' => 'required|exists:student_profiles,id',
        'attendances.*.status' => 'required|in:Present,Absent,Late',
        'attendances.*.remarks' => 'nullable|string',
    ];
}
}