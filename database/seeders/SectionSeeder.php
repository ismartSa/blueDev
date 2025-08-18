<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sections')->insert([
            [
                'title' => 'Introduction to PMP',
                'course_id' => 299,
                'order' => 1,
            ],
            [
                'title' => 'Framework',
                'course_id' => 299,
                'order' => 2,
            ],
            [
                'title' => 'Framework',
                'course_id' => 299,
                'order' => 3,
            ],
            [
                'title' => 'Domain People I ',
                'course_id' => 299,
                'order' => 4,
            ],

        ]);


        DB::table('lectures')->insert([
            [
                'uuid' => Str::uuid(),
                'name' => 'Framework 1',
                'title' => 'Framework 1',
                'course_id' => 299,
                'section_id' => 13,
                'description' => 'This section covers the basics of programming.',
                'video_url' => 'https://example.com/intro-to-programming',
                'duration' => 45,
                'order' => 1,
                'slug' => 'Framework_1',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Introduction 2',
                'title' => 'Introduction 2',
                'course_id' => 299,
                'section_id' => 12,
                'description' => 'This section covers the basics of programming.',
                'video_url' => 'https://example.com/intro-to-programming',
                'duration' => 45,
                'order' => 2,
                'slug' => 'Introduction_2',
            ],

            [
                'uuid' => Str::uuid(),
                'name' => 'Introduction 3',
                'title' => 'Introduction 3',
                'course_id' => 299,
                'section_id' => 12,
                'description' => 'This section covers the basics of programming.',
                'video_url' => 'https://example.com/intro-to-programming',
                'duration' => 45,
                'order' => 2,
                'slug' => 'Introduction_3',
            ],

        ]);

    }
}
