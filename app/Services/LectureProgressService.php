<?php

namespace App\Services;

use App\Models\LectureUserProgress;
use Illuminate\Support\Facades\Auth;

class LectureProgressService
{
    public function markLectureAsCompleted($lectureId)
    {
        $user = Auth::user();

        // تحديث أو إنشاء سجل التقدم الخاص بالمستخدم والمحاضرة
        $progress = LectureUserProgress::updateOrCreate(
            ['user_id' => $user->id, 'lecture_id' => $lectureId],
            ['completed' => true]
        );

        return ['message' => 'Lecture marked as completed'];
    }
}
