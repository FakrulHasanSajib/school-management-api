<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;

class NoticeController extends Controller
{
    // ১. নোটিশ তৈরি করা
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'published_at' => 'required|date',
            'recipient_type' => 'required|in:all,student,teacher'
        ]);

        $notice = Notice::create($validated);

        return response()->json([
            'message' => 'Notice published successfully',
            'data' => $notice
        ], 201);
    }

    // ২. নোটিশ দেখা
    public function index(): JsonResponse
    {
        $notices = Notice::latest()->get();
        return response()->json($notices, 200);
    }
}