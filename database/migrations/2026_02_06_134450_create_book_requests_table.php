<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::create('book_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // যে স্টুডেন্ট রিকোয়েস্ট করছে
        $table->foreignId('book_id')->constrained()->onDelete('cascade'); // যে বইয়ের জন্য রিকোয়েস্ট
        $table->date('request_date');
        $table->string('status')->default('Pending'); // Pending, Approved, Rejected
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_requests');
    }
};
