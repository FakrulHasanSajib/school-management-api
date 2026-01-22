<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ফরেন কি এরর এড়াতে আগে টেবিল মুছে নেওয়া ভালো
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('fee_types');

        // ১. Fee Types Table (টেস্টের জন্য এটি অবশ্যই লাগবে)
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // উদা: Tuition Fee
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ২. Fee Invoices Table
        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('fee_type_id')->constrained('fee_types')->onDelete('cascade'); // রিলেশন
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['Pending', 'Paid', 'Partial'])->default('Pending'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('fee_types');
    }
};