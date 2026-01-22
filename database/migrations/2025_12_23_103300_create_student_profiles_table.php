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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Users টেবিলের সাথে লিংক
    $table->foreignId('class_id')->constrained('classes'); // Classes টেবিলের সাথে লিংক
    $table->foreignId('section_id')->constrained('sections'); // Sections টেবিলের সাথে লিংক
    $table->foreignId('parent_id')->nullable()->constrained('users'); // যদি প্যারেন্ট ইউজার টেবিলে থাকে
    
    $table->string('admission_no')->unique();
    $table->string('roll_no');
    $table->date('dob');
    $table->string('gender');
    $table->text('address');
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
