<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoutineRequest;
use App\Services\RoutineService;
use App\Models\Routine;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

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
   // ✅ ১. সব রুটিন দেখার API (Filter সহ)
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Routine::with(['schoolClass', 'section', 'subject', 'teacher']);

            // যদি ক্লাস সিলেক্ট করা থাকে
            if ($request->class_id) {
                $query->where('class_id', $request->class_id);
            }

            // যদি সেকশন সিলেক্ট করা থাকে
            if ($request->section_id) {
                $query->where('section_id', $request->section_id);
            }

            $routines = $query->latest()->get();
            
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

    // ✅ নির্দিষ্ট একটি রুটিন দেখার জন্য (Edit পেজে ডাটা লোড করতে লাগবে)
    public function show($id): JsonResponse
    {
        try {
            $routine = Routine::findOrFail($id);
            return $this->success($routine, 'Routine fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Routine not found', 404);
        }
    }

    // ✅ রুটিন আপডেট করার জন্য
    public function update(StoreRoutineRequest $request, $id): JsonResponse
    {
        try {
            $routine = $this->routineService->updateRoutine($id, $request->validated());
            return $this->success($routine, 'Routine updated successfully');
        } catch (\Exception $e) {
            // কনফ্লিক্ট হলে 422 এরর রিটার্ন করবে
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
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