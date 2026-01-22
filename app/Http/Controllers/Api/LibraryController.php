<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LibraryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse; // আপনার প্রজেক্টে ApiResponse ট্রেইট থাকলে এটি ব্যবহার করুন
use Exception;

class LibraryController extends Controller
{
    use ApiResponse;

    protected $libraryService;

    public function __construct(LibraryService $libraryService)
    {
        $this->libraryService = $libraryService;
    }

    /**
     * নতুন বই যুক্ত করা
     */
    public function storeBook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'    => 'required|string',
            'author'   => 'required|string',
            'isbn'     => 'required|unique:books,isbn',
            'quantity' => 'required|integer|min:1',
            'category' => 'nullable|string'
        ]);

        $book = $this->libraryService->addBook($validated);

        // টেস্ট পাসের জন্য সঠিক মেসেজ এবং ২০১ স্ট্যাটাস কোড নিশ্চিত করা
        return response()->json([
            'status'  => true,
            'message' => 'Book added successfully',
            'data'    => $book
        ], 201);
    }

    /**
     * বই ইস্যু করা (স্টুডেন্ট বা টিচারকে)
     */
   // app/Http/Controllers/Api/LibraryController.php

// app/Http/Controllers/Api/LibraryController.php

// app/Http/Controllers/Api/LibraryController.php

public function issue(Request $request): JsonResponse
{
    $validated = $request->validate([
        'book_id'     => 'required|exists:books,id',
        'student_id'  => 'required|exists:student_profiles,id', // ✅ Nullable নয়, Required
        'return_date' => 'required|date' // ✅ return_date ব্যবহার করা হয়েছে
    ]);

    try {
        $issue = $this->libraryService->issueBook($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Book issued successfully',
            'data'    => $issue
        ], 201);
        
    } catch (Exception $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }
}
}