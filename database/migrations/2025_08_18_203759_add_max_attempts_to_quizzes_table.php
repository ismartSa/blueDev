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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->integer('max_attempts')->default(3)->after('time_limit');
            $table->text('instructions')->nullable()->after('is_active');
            $table->boolean('show_results')->default(true)->after('instructions');
            $table->boolean('randomize_questions')->default(false)->after('show_results');
            $table->boolean('randomize_answers')->default(false)->after('randomize_questions');
            $table->foreignId('lecture_id')->nullable()->constrained()->onDelete('cascade')->after('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['lecture_id']);
            $table->dropColumn([
                'max_attempts',
                'instructions', 
                'show_results',
                'randomize_questions',
                'randomize_answers',
                'lecture_id'
            ]);
        });
    }
};
