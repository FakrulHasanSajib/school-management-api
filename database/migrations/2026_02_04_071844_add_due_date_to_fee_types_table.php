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
    Schema::table('fee_types', function (Blueprint $table) {
        $table->date('due_date')->nullable()->after('amount'); // amount এর পরে date থাকবে
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_types', function (Blueprint $table) {
            //
        });
    }
};
