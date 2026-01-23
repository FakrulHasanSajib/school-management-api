<?php

namespace App\Services;

use App\Models\Routine;

class RoutineService
{
    // ✅ নতুন রুটিন তৈরি (Create)
    public function createRoutine(array $data)
    {
        $this->checkConflict($data); // কনফ্লিক্ট চেক ফাংশন কল
        return Routine::create($data);
    }

    // ✅ রুটিন আপডেট (Update) - নতুন যোগ করা হলো
    public function updateRoutine($id, array $data)
    {
        // কনফ্লিক্ট চেক (Current ID বাদ দিয়ে)
        $this->checkConflict($data, $id);

        $routine = Routine::findOrFail($id);
        $routine->update($data);
        return $routine;
    }

    // 🛠 কমন কনফ্লিক্ট চেকার ফাংশন (যাতে বারবার কোড লিখতে না হয়)
    private function checkConflict($data, $ignoreId = null)
    {
        // ১. শিক্ষকের কনফ্লিক্ট চেক
        $teacherConflict = Routine::where('teacher_id', $data['teacher_id'])
            ->where('day', $data['day'])
            ->when($ignoreId, function ($q) use ($ignoreId) {
                $q->where('id', '!=', $ignoreId); // ⚠️ আপডেট করার সময় নিজেকে বাদ দেবে
            })
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
            throw new \Exception('এই সময়ে শিক্ষকের অন্য ক্লাস আছে! (Teacher Conflict)');
        }

        // ২. সেকশনের কনফ্লিক্ট চেক
        $sectionConflict = Routine::where('section_id', $data['section_id'])
            ->where('day', $data['day'])
            ->when($ignoreId, function ($q) use ($ignoreId) {
                $q->where('id', '!=', $ignoreId); // ⚠️ আপডেট করার সময় নিজেকে বাদ দেবে
            })
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                      ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']]);
            })
            ->exists();

        if ($sectionConflict) {
            throw new \Exception('এই সময়ে এই সেকশনে অন্য ক্লাস আছে! (Section Conflict)');
        }
    }
}