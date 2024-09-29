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
        Schema::table('lectures', function (Blueprint $table) {
          // $table->string('slug')->unique()->after('title')->change();

        //  $table->string('slug')->default('temp-slug')->nullable(false);
        });

        // تحديث البيانات الموجودة لإضافة slug
        // $lectures = \App\Models\Lecture::all();

        // foreach ($lectures as $lecture) {
        //     $lecture->slug = Str::slug($lecture->title) .'-'. $lecture->id+18976 ;
        //     // dd($lecture->slug);
        //     $lecture->save();
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            //
        });
    }
};
