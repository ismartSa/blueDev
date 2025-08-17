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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->unsignedBigInteger('current_lecture_id')->nullable()->after('progress_percentage');
            $table->json('viewed_lectures')->nullable()->after('current_lecture_id');
            $table->json('completed_lectures')->nullable()->after('viewed_lectures');
            
            $table->foreign('current_lecture_id')->references('id')->on('lectures')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['current_lecture_id']);
            $table->dropColumn(['current_lecture_id', 'viewed_lectures', 'completed_lectures']);
        });
    }
};