<?php

namespace App\Services;

use App\Models\FeeInvoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class AccountService
{
    /**
     * ১. স্টুডেন্টের জন্য ইনভয়েস তৈরি করা
     */
    public function createInvoice(array $data)
    {
        return FeeInvoice::create([
            'student_id' => $data['student_id'],
            'fee_type_id' => $data['fee_type_id'],
            'title' => $data['title'],
            'amount' => $data['amount'],
            'due_date' => $data['due_date'],
            'status' => 'Unpaid' // ডিফল্ট স্ট্যাটাস [cite: 12]
        ]);
    }

    /**
     * ২. পেমেন্ট গ্রহণ করা
     */
    public function recordPayment(array $data)
    {
        return DB::transaction(function () use ($data) {
            // পেমেন্ট রেকর্ড তৈরি [cite: 13]
            $payment = Payment::create([
                'invoice_id' => $data['invoice_id'],
                'method' => $data['method'], // Bkash, Cash etc. [cite: 13]
                'amount' => $data['amount'],
                'transaction_id' => $data['transaction_id'],
                'paid_at' => now()
            ]);

            // ইনভয়েস স্ট্যাটাস আপডেট করা
            $invoice = FeeInvoice::find($data['invoice_id']);
            $invoice->update(['status' => 'Paid']);

            return $payment;
        });
    }
}