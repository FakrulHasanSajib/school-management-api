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
        // ১. ফরেন কি কনস্ট্রেইন্ট এর কারণে আগে চাইল্ড টেবিল (exam_marks) মুছতে হবে
        Schema::dropIfExists('exam_marks');
        Schema::dropIfExists('exams');

        // ২. Exams Table তৈরি
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('session'); 
            $table->date('start_date');
            $table->date('end_date'); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ৩. Exam Marks Table তৈরি
        Schema::create('exam_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade'); 
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->decimal('marks_obtained', 5, 2); 
            $table->string('grade')->nullable(); 
            $table->timestamps();

            // ইউনিক কনস্ট্রেইন্ট নিশ্চিত করা
            $table->unique(['exam_id', 'student_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_marks');
        Schema::dropIfExists('exams');
    }
};