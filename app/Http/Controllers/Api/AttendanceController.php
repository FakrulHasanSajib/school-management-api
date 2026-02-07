<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest; // ✅ আমাদের তৈরি করা রিকোয়েস্ট ফাইল
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse; // ✅ Trait যুক্ত করা হলো
use App\Models\Section;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ApiResponse;

    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * উপস্থিতি জমা দেওয়া
     */
    // ⚠️ পরিবর্তন: Request এর বদলে StoreAttendanceRequest ব্যবহার করা হয়েছে
// app/Http/Controllers/Api/AttendanceController.php

public function store(StoreAttendanceRequest $request): JsonResponse
{
    $data = $request->validated();
    $user = auth()->user();
    $section = \App\Models\Section::findOrFail($data['section_id']);

    // রোলটি ছোট হাতের করে নেওয়া যাতে টাইপিং এরর না হয়
    $userRole = strtolower($user->role);

    // আপনি কি সুপারঅ্যাডমিন বা অ্যাডমিন?
    $isBoss = ($userRole === 'superadmin' || $userRole === 'admin');

    // আপনি কি এই সেকশনের ইন-চার্জ?
    $isSectionTeacher = ($user->id == $section->teacher_id);

    if ($isBoss || $isSectionTeacher) {
        $this->attendanceService->storeAttendance($data);
        return $this->success(null, 'হাজিরা সফলভাবে সেভ হয়েছে।', 201);
    }

    // এরর মেসেজে রোল এবং আইডি দেখাচ্ছি যাতে ডিবাগ করতে সুবিধা হয়
    return $this->error("অনুমোদিত নন! আপনার রোল: $userRole (ID: {$user->id})", 403);
}
    /**
     * রিপোর্ট দেখা
     */
   // app/Http/Controllers/Api/AttendanceController.php

public function report(Request $request): JsonResponse
{
    $request->validate([
        'class_id' => 'required|exists:classes,id',
        'section_id' => 'required|exists:sections,id',
        'month' => 'required|numeric|between:1,12',
        'year' => 'required|numeric'
    ]);

    $report = $this->attendanceService->getMonthlyReport(
        $request->class_id,
        $request->section_id,
        $request->month,
        $request->year
    );

    return $this->success($report, 'Attendance report fetched successfully');
}
public function studentReportCard(Request $request, $studentId): JsonResponse
{
    $request->validate([
        'month' => 'required|numeric|between:1,12',
        'year' => 'required|numeric'
    ]);

    $data = $this->attendanceService->getStudentAttendanceSummary($studentId, $request->month, $request->year);
    return $this->success($data, 'Student report card fetched successfully');
}
// এই ফাংশনটি AttendanceController ক্লাসের ভেতরে যোগ করুন

public function getStudentsForAttendance(Request $request): JsonResponse
{
    $request->validate([
        'class_id' => 'required',
        'section_id' => 'required',
        'date' => 'required|date'
    ]);

    $date = $request->date;

    // ১. ওই সেকশনের সব স্টুডেন্ট আনা
    $students = \App\Models\StudentProfile::with('user:id,name')
        ->where('class_id', $request->class_id)
        ->where('section_id', $request->section_id)
        ->orderBy('roll_no')
        ->get()
        ->map(function ($student) use ($date) {
            // ২. চেক করা আজকের হাজিরা আছে কি না
            $attendance = \App\Models\Attendance::where('student_id', $student->user_id)
                ->where('date', $date)
                ->first();

            return [
                'student_id' => $student->user_id, // লগইন আইডি
                'name' => $student->user ? $student->user->name : 'Unknown',
                'roll' => $student->roll_no ?? 'N/A',
                // হাজিরা থাকলে সেই স্ট্যাটাস, না থাকলে ডিফল্ট 'Present'
                'status' => $attendance ? $attendance->status : 'Present',
            ];
        });

    return $this->success($students, 'Student list with attendance status fetched.');
}
}
