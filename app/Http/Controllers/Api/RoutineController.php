<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoutineRequest;
use App\Services\RoutineService;
use App\Models\Routine;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class RoutineController extends Controller
{
    use ApiResponse;

    protected $routineService;

    public function __construct(RoutineService $routineService)
    {
        $this->routineService = $routineService;
    }

    /**
     * ✅ ১. সব রুটিন দেখার API (List)
     */
    public function index(): JsonResponse
    {
        try {
            // আমরা 'schoolClass' রিলেশন ব্যবহার করছি কারণ আপনার মডেলের নাম SchoolClass.php
            // ফ্রন্টএন্ডে এটি অটোমেটিক 'school_class' হয়ে যাবে।
            $routines = Routine::with(['schoolClass', 'section', 'subject', 'teacher'])
                        ->latest()
                        ->get();
            
            return $this->success($routines, 'Routine list fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Server Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * ✅ ২. নতুন রুটিন তৈরি করার API (Create with Validation)
     */
    public function store(StoreRoutineRequest $request): JsonResponse
    {
        try {
            // RoutineService এর মাধ্যমে কনফ্লিক্ট চেক করে ডাটা সেভ করা হচ্ছে
            $routine = $this->routineService->createRoutine($request->validated());
            
            return $this->success($routine, 'Routine created successfully', 201);
        } catch (\Exception $e) {
            // যদি কনফ্লিক্ট হয় (যেমন: টিচার ওই সময়ে ব্যস্ত), তবে 422 এরর রিটার্ন করব
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * ✅ ৩. নির্দিষ্ট সেকশনের রুটিন দেখার API (Optional Feature)
     */
    public function getBySection($sectionId): JsonResponse
    {
        try {
            $routines = Routine::with(['schoolClass', 'section', 'subject', 'teacher'])
                        ->where('section_id', $sectionId)
                        ->orderBy('day') // দিন অনুযায়ী সাজানো
                        ->orderBy('start_time') // সময় অনুযায়ী সাজানো
                        ->get();

            return $this->success($routines, 'Section routine fetched successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    public function destroy($id) {
    Routine::destroy($id);
    return response()->json(['message' => 'Deleted successfully']);
}
}