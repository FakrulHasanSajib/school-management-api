<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeeInvoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
// ✅ আমাদের ম্যানুয়াল লাইব্রেরি ইম্পোর্ট
use App\Library\SslCommerz\SslCommerzNotification;

class SslCommerzController extends Controller
{
    // ১. পেমেন্ট শুরু করা
    public function index(Request $request)
    {
        $invoice = FeeInvoice::with('student.user')->find($request->invoice_id);

        if (!$invoice) {
            return response()->json(['status' => false, 'message' => 'Invoice not found']);
        }

        // পেমেন্ট ডাটা সাজানো
        $post_data = [
            'total_amount' => $invoice->due_amount,
            'currency' => "BDT",
            'tran_id' => uniqid(),

            // ✅ এই URL গুলো লারাভেলের APP_URL (.env) থেকে অটোমেটিক নিবে
            'success_url' => url('/api/payment/success'),
            'fail_url' => url('/api/payment/fail'),
            'cancel_url' => url('/api/payment/cancel'),

            // কাস্টমার ইনফো
            'cus_name' => $invoice->student->user->name ?? 'Student',
            'cus_email' => $invoice->student->user->email ?? 'student@school.com',
            'cus_add1' => "Dhaka",
            'cus_city' => "Dhaka",
            'cus_country' => "Bangladesh",
            'cus_phone' => $invoice->student->phone ?? '01700000000',

            // ইনভয়েস আইডি ট্র্যাকিং
            'value_a' => $invoice->id,
        ];

        $sslc = new SslCommerzNotification();
        $payment_options = $sslc->makePayment($post_data);

        if ($payment_options['status'] == 'success') {
            return response()->json([
                'status' => true,
                'url' => $payment_options['GatewayPageURL']
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Gateway Error: ' . ($payment_options['data'] ?? 'Unknown Error')
            ]);
        }
    }

    // ২. পেমেন্ট সফল হলে
    public function success(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $invoice_id = $request->input('value_a');

        // ✅ ফ্রন্টএন্ড URL .env ফাইল থেকে নেওয়া হচ্ছে (হোস্টিংয়ের জন্য জরুরি)
        // যদি .env তে না পায়, ডিফল্ট হিসেবে localhost:5174 নিবে
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5174');

        $sslc = new SslCommerzNotification();

        if ($sslc->orderValidate($request->all(), $tran_id, $amount)) {

            DB::beginTransaction();
            try {
                $invoice = FeeInvoice::find($invoice_id);

                if($invoice) {
                    // পেমেন্ট রেকর্ড
                    Payment::create([
                        'invoice_id' => $invoice_id,
                        'amount' => $amount,
                        'method' => 'SSLCommerz',
                        'transaction_id' => $tran_id,
                        'paid_at' => now()->toDateString(),
                    ]);

                    // ইনভয়েস আপডেট
                    $invoice->paid_amount += $amount;
                    $invoice->due_amount = $invoice->total_amount - $invoice->paid_amount;

                    if ($invoice->due_amount <= 0) {
                        $invoice->status = 'Paid';
                        $invoice->due_amount = 0;
                    } else {
                        $invoice->status = 'Partial';
                    }
                    $invoice->save();
                }

                DB::commit();

                // ✅ ডাইনামিক রিডাইরেক্ট
                return redirect($frontendUrl . '/student/fees?status=success');

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect($frontendUrl . '/student/fees?status=error');
            }
        } else {
            return redirect($frontendUrl . '/student/fees?status=failed');
        }
    }

    // ৩. পেমেন্ট ফেইল হলে
    public function fail(Request $request)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5174');
        return redirect($frontendUrl . '/student/fees?status=failed');
    }

    // ৪. পেমেন্ট ক্যান্সেল করলে
    public function cancel(Request $request)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5174');
        return redirect($frontendUrl . '/student/fees?status=cancel');
    }
}
