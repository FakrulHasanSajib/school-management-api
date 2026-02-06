<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LibraryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;
use App\Models\BookRequest;
use App\Models\BookIssue;
use App\Models\Book;
use App\Models\StudentProfile; // ✅ এখানে Student এর বদলে StudentProfile হবে
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
     * ১. সব বইয়ের লিস্ট দেখা
     */
    public function index()
    {
        $books = Book::latest()->get();

        return response()->json([
            'status' => true,
            'data' => $books
        ]);
    }

    /**
     * ২. নতুন বই যুক্ত করা
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

        return response()->json([
            'status'  => true,
            'message' => 'Book added successfully',
            'data'    => $book
        ], 201);
    }

    /**
     * ৩. বই ইস্যু করা (Fixed: StudentProfile Model Used)
     */
    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_id'     => 'required|exists:books,id',
            // ✅ টেবিলের নাম student_profiles হবে
            'student_id'  => 'required|exists:student_profiles,id',
            'return_date' => 'required|date'
        ]);

        try {
            // ১. স্টক চেক করা
            $book = Book::find($request->book_id);
            if ($book->quantity < 1) {
                return response()->json(['status' => false, 'message' => 'Book is out of stock'], 400);
            }

            // ২. ইস্যু রেকর্ড তৈরি
            $issue = BookIssue::create([
                'book_id' => $request->book_id,
                'student_id' => $request->student_id,
                'issue_date' => now(),
                'return_date' => $request->return_date,
                'status' => 'Issued'
            ]);

            // ৩. বইয়ের স্টক ১ কমানো
            $book->decrement('quantity');

            // ৪. রিকোয়েস্ট স্ট্যাটাস আপডেট (PENDING -> APPROVED)
            // ✅ এখানে Student::find() এর বদলে StudentProfile::find() হবে
            $student = StudentProfile::find($request->student_id);

            if ($student) {
                // ওই স্টুডেন্টের এই বইয়ের জন্য কোনো পেন্ডিং রিকোয়েস্ট থাকলে তা Approved করা
                BookRequest::where('user_id', $student->user_id)
                    ->where('book_id', $request->book_id)
                    ->where('status', 'Pending')
                    ->update(['status' => 'Approved']);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Book issued successfully and request approved!',
                'data'    => $issue
            ], 201);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ৪. ইস্যু করা বইয়ের লিস্ট দেখা
     */
    public function issuedBooks()
    {
        $issues = BookIssue::with(['book', 'student.user'])
                    ->orderBy('id', 'desc')
                    ->get();

        return response()->json([
            'status' => true,
            'data' => $issues
        ]);
    }

    /**
     * ৫. বই ফেরত নেওয়া
     */
    public function returnBook($id)
    {
        try {
            $issue = BookIssue::find($id);

            if (!$issue) {
                return response()->json(['status' => false, 'message' => 'Issue record not found'], 404);
            }

            if ($issue->status === 'Returned') {
                return response()->json(['status' => false, 'message' => 'Book already returned'], 400);
            }

            $issue->status = 'Returned';
            $issue->returned_at = now();
            $issue->save();

            $book = Book::find($issue->book_id);
            if ($book) {
                $book->increment('quantity');
            }

            return response()->json(['status' => true, 'message' => 'Book returned successfully']);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ৬. স্টুডেন্ট রিকোয়েস্ট পাঠানো
     */
    public function requestBook(Request $request)
    {
        $request->validate(['book_id' => 'required|exists:books,id']);

        $exists = BookRequest::where('user_id', $request->user()->id)
                    ->where('book_id', $request->book_id)
                    ->whereIn('status', ['Pending', 'Approved'])
                    ->exists();

        if ($exists) {
            return response()->json(['status' => false, 'message' => 'You already requested this book.'], 400);
        }

        BookRequest::create([
            'user_id' => $request->user()->id,
            'book_id' => $request->book_id,
            'request_date' => now(),
            'status' => 'Pending'
        ]);

        return response()->json(['status' => true, 'message' => 'Book request sent successfully!']);
    }

    /**
     * ৭. স্টুডেন্ট নিজের রিকোয়েস্ট দেখবে
     */
    public function getMyRequests(Request $request)
    {
        $requests = BookRequest::where('user_id', $request->user()->id)
                    ->with('book')
                    ->latest()
                    ->get();

        return response()->json(['status' => true, 'data' => $requests]);
    }

    /**
     * ৮. লাইব্রেরিয়ান সব রিকোয়েস্ট দেখবে
     */
    public function getAllRequests()
    {
        $requests = BookRequest::with(['user', 'book'])
                    ->where('status', 'Pending')
                    ->latest()
                    ->get();

        return response()->json(['status' => true, 'data' => $requests]);
    }
}
