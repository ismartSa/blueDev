<?php

namespace App\Services;

class BreadcrumbService
{
    public function generateCourseBreadcrumbs($course, $courseSlug, $additionalItems = [])
    {
        $breadcrumbs = [
            [
                'title' => __('common.dashboard'),
                'url' => route('dashboard'),
                'active' => false
            ],
            [
                'title' => __('courses.list'),
                'url' => route('courses.index'),
                'active' => false
            ],
            [
                'title' => $course->title,
                'url' => route('course.details', ['id' => $course->id, 'courseSlug' => $courseSlug]),
                'active' => true
            ]
        ];

        return array_merge($breadcrumbs, $additionalItems);
    }
}
