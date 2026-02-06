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
    Schema::create('leaves', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // যে ইউজার আবেদন করছে
        $table->string('type'); // Sick, Casual, etc.
        $table->date('start_date');
        $table->date('end_date');
        $table->text('reason'); // ✅ এই কলামটি মিসিং থাকলে এরর দিবে
        $table->string('status')->default('Pending'); // Pending, Approved, Rejected
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
