<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeeInvoice;
use App\Models\FeeType;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    // ১. ফি টাইপ লিস্ট দেখা
    public function getFeeTypes(): JsonResponse
    {
        $types = FeeType::all();
        return response()->json(['status' => true, 'data' => $types]);
    }

    // ২. নতুন ফি টাইপ তৈরি করা
    public function storeFeeType(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'amount' => 'required|numeric',
            'description' => 'nullable|string'
        ]);

        $type = FeeType::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'description' => $request->description
        ]);

        return response()->json(['status' => true, 'message' => 'Fee Type Created', 'data' => $type], 201);
    }

    // ৩. ইনভয়েস জেনারেট করা (FIXED for Database Schema)
    public function generateInvoice(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'due_date' => 'required|date'
        ]);

        $feeType = FeeType::findOrFail($request->fee_type_id);

        // ডুপ্লিকেট চেক: একই স্টুডেন্টকে একই মাসে একই ফি যেন দুবার ধরা না হয়
        $exists = FeeInvoice::where('student_id', $request->student_id)
            ->where('fee_type_id', $request->fee_type_id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->exists();

        if ($exists) {
            return response()->json(['status' => false, 'message' => 'এই মাসে এই ছাত্রের জন্য এই ফি ইতিমধ্যে তৈরি করা হয়েছে।'], 400);
        }

        // ✅ ফিক্স: 'title' বাদ দেওয়া হয়েছে (ডাটাবেসে নেই)
        // ✅ ফিক্স: স্ট্যাটাস 'Unpaid' এর বদলে 'Pending' দেওয়া হয়েছে (ডাটাবেস ENUM অনুযায়ী)
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

    // ৪. ছাত্রের সব ইনভয়েস দেখা (পেমেন্ট নেওয়ার জন্য)
    public function getStudentInvoices($student_id): JsonResponse
    {
        // 'feeType' রিলেশন লোড করা হচ্ছে যাতে ফ্রন্টএন্ডে নাম (Monthly Fee) দেখানো যায়
        $invoices = FeeInvoice::with('feeType')
            ->where('student_id', $student_id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['status' => true, 'data' => $invoices]);
    }

    // ৫. ফি পেমেন্ট রিসিভ করা (FIXED)
    public function payInvoice(Request $request): JsonResponse
    {
        $request->validate([
            'fee_invoice_id' => 'required|exists:fee_invoices,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string'
        ]);

        $invoice = FeeInvoice::findOrFail($request->fee_invoice_id);

        // যদি ইতিমধ্যে পরিশোধ হয়ে থাকে
        if ($invoice->status === 'Paid' || $invoice->due_amount <= 0) {
            return response()->json(['status' => false, 'message' => 'This invoice is already paid.'], 400);
        }

        // অতিরিক্ত পেমেন্ট চেক
        if ($request->amount > $invoice->due_amount) {
            return response()->json(['status' => false, 'message' => 'Amount exceeds due amount (সর্বোচ্চ বকেয়া: ' . $invoice->due_amount . ')'], 422);
        }

        // ১. পেমেন্ট রেকর্ড তৈরি
        $payment = Payment::create([
            'invoice_id'     => $request->fee_invoice_id, 
            'amount'         => $request->amount,
            'method'         => $request->payment_method,
            'transaction_id' => $request->transaction_id ?? 'TRX-' . time() . rand(100,999), // ইউনিক ট্রানজেকশন আইডি
            'paid_at'        => now()->toDateString(), // ডাটাবেসে date টাইপ আছে, তাই Date স্ট্রিং পাঠানো ভালো
        ]);

        // ২. ইনভয়েস আপডেট (ব্যালেন্স কমানো)
        $invoice->paid_amount += $request->amount;
        $invoice->due_amount = $invoice->total_amount - $invoice->paid_amount;
        
        // স্ট্যাটাস আপডেট (ডাটাবেস ENUM অনুযায়ী)
        if ($invoice->due_amount <= 0) {
            $invoice->status = 'Paid';
            $invoice->due_amount = 0; // সেফটি
        } else {
            $invoice->status = 'Partial';
        }
        
        $invoice->save();

        return response()->json([
            'status' => true, 
            'message' => 'Payment received successfully', 
            'data' => $invoice
        ], 201);
    }
    public function getAllInvoices(Request $request): JsonResponse
    {
        // সব ইনভয়েস আনবে, সাথে স্টুডেন্টের নাম এবং ফি টাইপ থাকবে
        $invoices = FeeInvoice::with(['student.user', 'feeType'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['status' => true, 'data' => $invoices]);
    }
}