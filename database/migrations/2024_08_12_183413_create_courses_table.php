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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained() ->onDelete('cascade');
            $table->string('title');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description');
            $table->string('body');
            $table->integer('duration');
            $table->string('image')->nullable();
            $table->boolean('status')->default(false);
            $table->string('intro_video')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
