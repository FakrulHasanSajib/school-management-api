<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // আগের ভুল টেবিল থাকলে ডিলিট করে দিবে
        Schema::dropIfExists('payrolls');

        // নতুন সঠিক টেবিল তৈরি করবে
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            // staff_id এর বদলে user_id (কারণ শিক্ষকরা users টেবিলে আছেন)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('salary_month'); // যেমন: "2026-02"
            $table->decimal('amount', 10, 2); // বেসিক স্যালারি

            // বোনাস ও ডিডাকশন ফিল্ডগুলো এখন থাকবে
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('deduction', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2); // ফাইনাল এমাউন্ট

            $table->string('status')->default('Paid');
            $table->text('note')->nullable();
            $table->date('payment_date');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
};
