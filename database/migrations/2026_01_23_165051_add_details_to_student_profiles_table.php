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
    Schema::table('student_profiles', function (Blueprint $table) {
        // ৩টি নতুন কলাম যোগ করা হলো
        $table->string('phone')->nullable()->after('address'); // ফোন নাম্বার
        $table->string('blood_group')->nullable()->after('phone'); // ব্লাড গ্রুপ
        $table->string('image')->nullable()->after('blood_group'); // ছবির নাম/পাথ
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            //
        });
    }
};
