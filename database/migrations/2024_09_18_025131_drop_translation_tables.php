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
        Schema::dropIfExists('answer_translations');
        Schema::dropIfExists('question_translations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('question_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('locale', 5);
            $table->text('question_title');
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['question_id', 'locale']);
        });

        Schema::create('answer_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained()->onDelete('cascade');
            $table->string('locale', 5);
            $table->text('answer');
            $table->timestamps();

            $table->unique(['answer_id', 'locale']);
        });
    }
};
