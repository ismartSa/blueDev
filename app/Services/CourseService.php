<?php

namespace App\Services;

use App\Models\Course;
use App\Exceptions\CourseException;

class CourseService
{
    public function create(array $data)
    {
        try {
            return Course::create($data);
        } catch (\Exception $e) {
            throw new CourseException('فشل في إنشاء الدورة: ' . $e->getMessage());
        }
    }

    public function update(Course $course, array $data)
    {
        try {
            $course->update($data);
            return $course;
        } catch (\Exception $e) {
            throw new CourseException('فشل في تحديث الدورة: ' . $e->getMessage());
        }
    }
}
