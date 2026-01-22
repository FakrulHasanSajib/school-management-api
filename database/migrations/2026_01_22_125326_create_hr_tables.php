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
    // ১. Designations Table (পদবি এবং বেসিক স্যালারি)
    Schema::create('designations', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // উদা: Manager, Driver
        $table->decimal('basic_salary', 10, 2);
        $table->timestamps();
    });

    // ২. Staff Table (স্টাফ ইনফরমেশন)
    Schema::create('staff', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('designation_id')->constrained('designations')->onDelete('cascade');
        $table->date('joining_date');
        $table->string('address')->nullable();
        $table->timestamps();
    });

    // ৩. Payrolls Table (স্যালারি পেমেন্ট রেকর্ড)
    Schema::create('payrolls', function (Blueprint $table) {
        $table->id();
        $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
        $table->string('month'); // January, February...
        $table->year('year');
        $table->decimal('amount', 10, 2);
        $table->string('status')->default('Paid');
        $table->timestamps();
    });
}
};
