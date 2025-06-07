<?php

namespace App\Http\Controllers\Course; // Changed namespace

use App\Http\Controllers\Controller; // Added use statement for base Controller
use App\Models\Enrollment;
use Carbon\Carbon;

class CourseEnrollmentController extends Controller
{
    public function enroll($courseId)
    {
        // ... existing enroll method code ...
    }
}
