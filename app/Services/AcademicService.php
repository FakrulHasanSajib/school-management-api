<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;

class AcademicService
{
    // ১. নতুন ক্লাস তৈরি করা
    public function createClass(array $data)
    {
        return SchoolClass::create([
            'name' => $data['name'],
            'numeric_value' => $data['numeric_value']
        ]);
    }

    // ২. ক্লাসে সেকশন যুক্ত করা
    public function addSectionToClass(array $data)
    {
        return Section::create([
            'class_id' => $data['class_id'],
            'name' => $data['name'],       // Example: "Section A"
            'capacity' => $data['capacity'] ?? 50
        ]);
    }

    // ৩. সব ক্লাস এবং তাদের সেকশন লিস্ট আনা
    public function getAllClassesWithSections()
    {
        // Eager Loading ব্যবহার করে সেকশনসহ ক্লাস আনা হচ্ছে
        return SchoolClass::with('sections')->orderBy('numeric_value')->get();
    }
    public function createSubject(array $data)
    {
        return Subject::create([
            'class_id' => $data['class_id'],
            'name' => $data['name'],
            'code' => $data['code'],
            'type' => $data['type'] ?? 'Theory', // ডিফল্ট ভ্যালু
        ]);
    }

    /**
     * ৪. নির্দিষ্ট ক্লাসের সব সাবজেক্ট দেখা
     */
    public function getSubjectsByClass($classId)
    {
        return Subject::where('class_id', $classId)->get();
    }
}