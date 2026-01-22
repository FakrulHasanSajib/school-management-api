<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookIssue;
use Exception;
use Illuminate\Support\Facades\DB;

class LibraryService
{
    /**
     * ১. নতুন বই যুক্ত করা
     */
    public function addBook(array $data)
    {
        return Book::create($data);
    }

    /**
     * ২. বই ইস্যু করা (ইস্যু করার আগে স্টক চেক করা)
     */
   // app/Services/LibraryService.php

public function issueBook(array $data)
{
    $book = \App\Models\Book::findOrFail($data['book_id']);
    
    if ($book->quantity <= 0) {
        throw new Exception("This book is currently out of stock.");
    }

    return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $book) {
        $book->decrement('quantity');

        return \App\Models\BookIssue::create([
            'book_id'     => $data['book_id'],
            'student_id'  => $data['student_id'], // ✅ সরাসরি ভ্যালু
            'issue_date'  => now()->toDateString(),
            'return_date' => $data['return_date'], // ✅ return_date
            'status'      => 'Issued'
        ]);
    });
}

    /**
     * ৩. বই রিটার্ন করা
     */
    public function returnBook($issueId)
    {
        $issue = BookIssue::findOrFail($issueId);
        
        if ($issue->status === 'Returned') {
            throw new Exception("This book has already been returned.");
        }

        return DB::transaction(function () use ($issue) {
            $issue->update([
                'returned_at' => now()->toDateString(), // ✅ প্রকৃত ফেরত দেওয়ার তারিখ
                'status'      => 'Returned'
            ]);

            // বইয়ের স্টক ১ বাড়ানো
            Book::where('id', $issue->book_id)->increment('quantity');
            
            return $issue;
        });
    }
}