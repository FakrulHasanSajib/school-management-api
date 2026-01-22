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
    Schema::table('attendances', function (Blueprint $table) {
        // student_id এর পরে class_id এবং section_id যুক্ত করা হচ্ছে
        $table->foreignId('class_id')->nullable()->after('student_id')->constrained('classes')->onDelete('cascade');
        $table->foreignId('section_id')->nullable()->after('class_id')->constrained('sections')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('attendances', function (Blueprint $table) {
        $table->dropForeign(['class_id']);
        $table->dropForeign(['section_id']);
        $table->dropColumn(['class_id', 'section_id']);
    });
}
};