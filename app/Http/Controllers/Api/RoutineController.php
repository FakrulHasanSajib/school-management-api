<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoutineRequest;
use App\Services\RoutineService;
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

    public function store(StoreRoutineRequest $request): JsonResponse
    {
        try {
            $routine = $this->routineService->createRoutine($request->validated());
            return $this->success($routine, 'Routine created successfully', 201);
        } catch (\Exception $e) {
            // যদি কনফ্লিক্ট হয়, তবে 422 এরর রিটার্ন করব
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // সেকশন অনুযায়ী রুটিন দেখার API (Optional)
    public function getBySection($sectionId)
    {
        // লজিক পরে লিখলেও হবে
    }
}