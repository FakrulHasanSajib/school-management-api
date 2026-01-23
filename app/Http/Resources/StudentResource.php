<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'id' => $this->id,
        'name' => $this->user->name,
        'email' => $this->user->email,
        'admission_no' => $this->admission_no,
        'roll_no' => $this->roll_no,
        'class' => $this->schoolClass->name,
        'section' => $this->section->name,
        'dob' => $this->dob,
        'gender' => $this->gender,
        'address' => $this->address,
        // 👇 নাম দেখানোর জন্য
        'class' => $this->schoolClass->name,
        'section' => $this->section->name,

        // 👇 এডিট করার জন্য আইডি (নতুন যোগ করা হয়েছে)
        'class_id' => $this->class_id,
        'section_id' => $this->section_id,
    ];
    }
}
