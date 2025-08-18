<?php

namespace App\Repositories;

use App\Models\Course;

class CourseRepository
{
    public function findBySlug($slug)
    {
        return Course::with(['sections.lectures', 'enrollments'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function getActiveCourses()
    {
        return Course::where('status', 'active')
            ->with(['sections'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
}
