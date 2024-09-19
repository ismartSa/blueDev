<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create new questions table
        Schema::create('questions_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->string('question_title_en');
            $table->string('question_title_ar');
            $table->integer('question_type_id');
            $table->integer('correct_answers_required');
            $table->timestamps();
        });

        // Copy data from old table to new table
        DB::table('questions_new')->insertUsing(
            ['id', 'quiz_id', 'question_title_en', 'question_title_ar', 'question_type_id', 'correct_answers_required', 'created_at', 'updated_at'],
            DB::table('questions')
                ->select('id', 'quiz_id', 'question_title', DB::raw('question_title as question_title_ar'), 'question_type_id', 'correct_answers_required', 'created_at', 'updated_at')
        );

        // Create new answers table
        Schema::create('answers_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions_new')->onDelete('cascade');
            $table->string('answer_en');
            $table->string('answer_ar');
            $table->boolean('is_correct');
            $table->timestamps();
        });

        // Copy data from old table to new table
        DB::table('answers_new')->insertUsing(
            ['id', 'question_id', 'answer_en', 'answer_ar', 'is_correct', 'created_at', 'updated_at'],
            DB::table('answers')
                ->select('id', 'question_id', 'answer', DB::raw('answer as answer_ar'), 'is_correct', 'created_at', 'updated_at')
        );

        // Drop old tables
        Schema::drop('answers');
        Schema::drop('questions');

        // Rename new tables to original names
        Schema::rename('questions_new', 'questions');
        Schema::rename('answers_new', 'answers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This down method will not perfectly reverse the changes
        // It will keep the new structure but remove the Arabic fields
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('question_title_ar');
            $table->renameColumn('question_title_en', 'question_title');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn('answer_ar');
            $table->renameColumn('answer_en', 'answer');
        });
    }
};
