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
    Schema::create('general_settings', function (Blueprint $table) {
        $table->id();
        $table->string('school_name')->nullable();
        $table->string('school_address')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('school_logo')->nullable(); // লোগোর পাথ রাখার জন্য
        $table->string('principal_signature')->nullable(); // স্বাক্ষরের পাথ রাখার জন্য
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
