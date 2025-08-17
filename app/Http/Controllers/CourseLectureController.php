<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecture;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Handles course lecture operations including viewing lectures,
 * tracking progress, and managing lecture completion.
 */
class CourseLectureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a specific lesson
     */
    public function show(Course $course, Lecture $lecture)
    {
        $user = Auth::user();

        // Check if user is enrolled in the course
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'You must be enrolled in this course to access lectures.');
        }

        // Verify lecture belongs to the course
        if ($lecture->course_id !== $course->id) {
            abort(404);
        }

        // Get all lectures for navigation
        $lectures = $course->lectures()->orderBy('order')->get();

        // Get current lecture index
        $currentIndex = $lectures->search(function ($item) use ($lecture) {
            return $item->id === $lecture->id;
        });

        // Get previous and next lectures
        $previousLecture = $currentIndex > 0 ? $lectures[$currentIndex - 1] : null;
        $nextLecture = $currentIndex < $lectures->count() - 1 ? $lectures[$currentIndex + 1] : null;

        // Mark lecture as viewed if not already
        $this->markLectureAsViewed($enrollment, $lecture);

        return Inertia::render('Courses/Lecture', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'instructor' => $course->instructor->name,
            ],
            'lecture' => [
                'id' => $lecture->id,
                'title' => $lecture->title,
                'description' => $lecture->description,
                'video_url' => $lecture->video_url,
                'duration' => $lecture->duration,
                'order' => $lecture->order,
            ],
            'lectures' => $lectures->map(function ($l) {
                return [
                    'id' => $l->id,
                    'title' => $l->title,
                    'order' => $l->order,
                    'duration' => $l->duration,
                ];
            }),
            'navigation' => [
                'previous' => $previousLecture ? [
                    'id' => $previousLecture->id,
                    'title' => $previousLecture->title,
                    'slug' => $previousLecture->slug ?? $previousLecture->id,
                ] : null,
                'next' => $nextLecture ? [
                    'id' => $nextLecture->id,
                    'title' => $nextLecture->title,
                    'slug' => $nextLecture->slug ?? $nextLecture->id,
                ] : null,
            ],
            'enrollment' => [
                'progress' => $enrollment->progress,
                'completed_at' => $enrollment->completed_at,
            ],
        ]);
    }

    /**
     * Mark lecture as completed
     */
    public function complete(Course $course, Lecture $lecture)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        // Mark lecture as completed
        $this->markLectureAsCompleted($enrollment, $lecture);

        // Calculate and update course progress
        $totalLectures = $course->lectures()->count();
        $completedLectures = $this->getCompletedLecturesCount($enrollment);
        $progress = $totalLectures > 0 ? ($completedLectures / $totalLectures) * 100 : 0;

        $enrollment->update([
            'progress' => $progress,
            'completed_at' => $progress >= 100 ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lecture marked as completed',
            'progress' => $progress,
            'completed_lectures' => $completedLectures,
            'total_lectures' => $totalLectures,
        ]);
    }

    /**
     * Get lecture progress for a course
     */
    public function progress(Course $course)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        $lectures = $course->lectures()->orderBy('order')->get();
        $completedLectures = $this->getCompletedLecturesArray($enrollment);

        $lecturesProgress = $lectures->map(function ($lecture) use ($completedLectures) {
            return [
                'id' => $lecture->id,
                'title' => $lecture->title,
                'order' => $lecture->order,
                'duration' => $lecture->duration,
                'is_completed' => in_array($lecture->id, $completedLectures),
            ];
        });

        return response()->json([
            'success' => true,
            'progress' => $enrollment->progress,
            'lectures' => $lecturesProgress,
            'completed_lectures' => count($completedLectures),
            'total_lectures' => $lectures->count(),
        ]);
    }

    /**
     * Mark lecture as viewed (for tracking purposes)
     */
    private function markLectureAsViewed($enrollment, $lecture)
    {
        $viewedLectures = json_decode($enrollment->viewed_lectures ?? '[]', true);

        if (!in_array($lecture->id, $viewedLectures)) {
            $viewedLectures[] = $lecture->id;
            $enrollment->update([
                'viewed_lectures' => json_encode($viewedLectures)
            ]);
        }
    }

    /**
     * Mark lecture as completed
     */
    private function markLectureAsCompleted($enrollment, $lecture)
    {
        $completedLectures = json_decode($enrollment->completed_lectures ?? '[]', true);

        if (!in_array($lecture->id, $completedLectures)) {
            $completedLectures[] = $lecture->id;
            $enrollment->update([
                'completed_lectures' => json_encode($completedLectures)
            ]);
        }
    }

    /**
     * Get count of completed lectures
     */
    private function getCompletedLecturesCount($enrollment)
    {
        $completedLectures = json_decode($enrollment->completed_lectures ?? '[]', true);
        return count($completedLectures);
    }

    /**
     * Get array of completed lecture IDs
     */
    private function getCompletedLecturesArray($enrollment)
    {
        return json_decode($enrollment->completed_lectures ?? '[]', true);
    }
}