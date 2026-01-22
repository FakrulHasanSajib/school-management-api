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
    // ১. Expense Categories (যেমন: Utility, Maintenance)
    Schema::create('expense_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->text('description')->nullable();
        $table->timestamps();
    });

    // ২. Expenses (প্রতিদিনের খরচ)
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('cascade');
        $table->decimal('amount', 10, 2);
        $table->date('expense_date');
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_tables');
    }
};
