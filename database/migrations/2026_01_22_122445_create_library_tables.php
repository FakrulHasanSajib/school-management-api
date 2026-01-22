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
    // ১. Books Table
    Schema::create('books', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('author');
        $table->string('isbn')->unique();
        $table->integer('quantity')->default(0);
        $table->string('category')->nullable();
        $table->timestamps();
    });

    // ২. Book Issues Table
    Schema::create('book_issues', function (Blueprint $table) {
        $table->id();
        $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
        // student_id এখানে বাধ্যতামূলক (nullable নয়)
        $table->foreignId('student_id')->constrained('student_profiles')->onDelete('cascade');
        $table->date('issue_date');
        $table->date('return_date'); // ✅ আমাদের টার্গেট কলাম
        $table->date('returned_at')->nullable();
        $table->string('status')->default('Issued');
        $table->timestamps();
    });
}

public function down(): void
{
    // টেবিলগুলো মুছে ফেলার সঠিক নিয়ম
    Schema::dropIfExists('book_issues');
    Schema::dropIfExists('books');
}
};
