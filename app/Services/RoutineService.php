<?php

namespace App\Services;

use App\Models\Routine;

class RoutineService
{
    public function createRoutine(array $data)
    {
        // ১. কনফ্লিক্ট চেক: এই শিক্ষকের কি ওই সময়ে অন্য ক্লাস আছে?
        $teacherConflict = Routine::where('teacher_id', $data['teacher_id'])
            ->where('day', $data['day'])
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                      ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                      ->orWhere(function ($q) use ($data) {
                          $q->where('start_time', '<=', $data['start_time'])
                            ->where('end_time', '>=', $data['end_time']);
                      });
            })
            ->exists();

        if ($teacherConflict) {
            throw new \Exception('Teacher is already booked at this time!');
        }

        // ২. কনফ্লিক্ট চেক: এই সেকশনে কি ওই সময়ে অন্য ক্লাস আছে?
        $sectionConflict = Routine::where('section_id', $data['section_id'])
            ->where('day', $data['day'])
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                      ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']]);
            })
            ->exists();

        if ($sectionConflict) {
            throw new \Exception('This section already has a class at this time!');
        }

        // ৩. সব ঠিক থাকলে রুটিন তৈরি করো
        return Routine::create($data);
    }
}