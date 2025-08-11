<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('domain')->nullable()->after('section_id');
            $table->string('chapter')->nullable()->after('domain');
            $table->enum('quiz_type', ['practice', 'chapter_test', 'final', 'assessment'])->default('practice')->after('chapter');
            $table->integer('order')->default(0)->after('quiz_type');
            $table->index(['domain', 'chapter', 'order']);
        });
    }

    public function down()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['domain', 'chapter', 'order']);
            $table->dropColumn(['domain', 'chapter', 'quiz_type', 'order']);
        });
    }
};
