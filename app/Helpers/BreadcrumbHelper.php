<?php

namespace App\Helpers;

class BreadcrumbHelper
{
    public static function generateCourseBreadcrumbs($course, $courseSlug)
    {
        return [
            ['label' => __('dashboard'), 'href' => route('dashboard')],
            ['label' => __('courses.list'), 'href' => route('courses.index')],
            ['label' => $course->title, 'href' => route('course.details', ['id' => $course->id, 'courseSlug' => $courseSlug])]
        ];
    }
}
