<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Section;
use App\Models\Lecture;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sections using firstOrCreate to prevent duplicates
        $sections = [
            ['title' => 'Introduction to PMP', 'course_id' => 2, 'order' => 1],
            ['title' => 'Framework', 'course_id' => 2, 'order' => 2],
            ['title' => 'Advanced Framework', 'course_id' => 2, 'order' => 3], // Fixed duplicate title
            ['title' => 'Domain People I', 'course_id' => 2, 'order' => 4],
        ];

        $createdSections = [];
        foreach ($sections as $sectionData) {
            $section = Section::firstOrCreate(
                ['title' => $sectionData['title'], 'course_id' => $sectionData['course_id']],
                $sectionData
            );
            $createdSections[] = $section;
        }

        // Create lectures using firstOrCreate to prevent duplicates
        $lectures = [
            [
                'name' => 'Framework 1',
                'title' => 'Framework 1',
                'course_id' => 2,
                'section_id' => $createdSections[1]->id, // Framework section
                'description' => 'This section covers the framework basics.',
                'video_url' => 'https://example.com/framework-1',
                'duration' => 45,
                'order' => 1,
                'slug' => 'framework-1',
            ],
            [
                'name' => 'Introduction 2',
                'title' => 'Introduction 2',
                'course_id' => 2,
                'section_id' => $createdSections[0]->id, // Introduction section
                'description' => 'This section covers introduction concepts.',
                'video_url' => 'https://example.com/introduction-2',
                'duration' => 45,
                'order' => 2,
                'slug' => 'introduction-2',
            ],
            [
                'name' => 'Introduction 3',
                'title' => 'Introduction 3',
                'course_id' => 2,
                'section_id' => $createdSections[0]->id, // Introduction section
                'description' => 'This section covers advanced introduction topics.',
                'video_url' => 'https://example.com/introduction-3',
                'duration' => 45,
                'order' => 3,
                'slug' => 'introduction-3',
            ],
        ];

        foreach ($lectures as $lectureData) {
            $lectureData['uuid'] = Str::uuid();
            Lecture::firstOrCreate(
                ['slug' => $lectureData['slug'], 'course_id' => $lectureData['course_id']],
                $lectureData
            );
        }
    }
}
