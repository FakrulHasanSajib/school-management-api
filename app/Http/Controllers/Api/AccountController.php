<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeeInvoice; // ✅ এটি নিশ্চিত করুন
use App\Models\FeeType;    // ✅ এটি নিশ্চিত করুন
use App\Models\Payment;    // ✅ এটি নিশ্চিত করুন
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    /**
     * ইনভয়েস জেনারেট করা
     */
    public function generateInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'due_date' => 'required|date'
        ]);

        $feeType = FeeType::findOrFail($request->fee_type_id);

        $invoice = FeeInvoice::create([
            'student_id' => $request->student_id,
            'fee_type_id' => $request->fee_type_id,
            'total_amount' => $feeType->amount,
            'due_amount' => $feeType->amount,
            'paid_amount' => 0,
            'due_date' => $request->due_date,
            'status' => 'Pending'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Invoice generated successfully',
            'data' => $invoice
        ], 201);
    }

    /**
     * ফি পেমেন্ট রিসিভ করা
     */
// app/Http/Controllers/Api/AccountController.php

// app/Http/Controllers/Api/AccountController.php

public function payInvoice(Request $request): JsonResponse
{
    $request->validate([
        'fee_invoice_id' => 'required|exists:fee_invoices,id',
        'amount' => 'required|numeric|min:1',
        'payment_method' => 'required|string',
        'transaction_id' => 'required|unique:payments,transaction_id'
    ]);

    $invoice = FeeInvoice::findOrFail($request->fee_invoice_id);

    // পেমেন্ট রেকর্ড তৈরি (paid_at সহ)
    Payment::create([
        'invoice_id'     => $request->fee_invoice_id, 
        'amount'         => $request->amount,
        'method'         => $request->payment_method,
        'transaction_id' => $request->transaction_id,
        'paid_at'        => now(), // ✅ এই লাইনটি NOT NULL এরর ফিক্স করবে
    ]);

    // ইনভয়েসের ব্যালেন্স আপডেট
    $invoice->paid_amount += $request->amount;
    $invoice->due_amount = $invoice->total_amount - $invoice->paid_amount;
    
    // স্ট্যাটাস আপডেট
    if ($invoice->due_amount <= 0) {
        $invoice->status = 'Paid';
    } else {
        $invoice->status = 'Partial';
    }
    
    $invoice->save();

    return response()->json([
        'status' => true, 
        'message' => 'Payment recorded successfully', 
        'data' => $invoice
    ], 201);
}
}