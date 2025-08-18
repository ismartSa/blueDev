<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CourseRecommendationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get course suggestions for the user
     */
    public function suggestions(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        
        $filters = $this->validateFilters($request);
        $limit = $request->get('limit', 12);
        
        $suggestions = $this->generateSuggestions($user, $limit, $filters);
        $categories = $this->getActiveCategories();
        
        $responseData = [
            'courses' => $suggestions,
            'hasMore' => $suggestions->count() === $limit,
        ];
        
        if ($request->expectsJson()) {
            return response()->json($responseData);
        }
        
        return Inertia::render('Courses/Suggestions', array_merge($responseData, [
            'categories' => $categories,
            'totalCourses' => $this->getActiveCourseCount(),
        ]));
    }

    /**
     * Validate and sanitize request filters
     */
    private function validateFilters(Request $request): array
    {
        return $request->only(['category', 'level', 'price', 'sort', 'offset']);
    }

    /**
     * Get active categories for filter dropdown
     */
    private function getActiveCategories()
    {
        return Category::whereHas('courses', function ($query) {
            $query->where('status', true);
        })->pluck('name')->unique()->values();
    }

    /**
     * Get total count of active courses
     */
    private function getActiveCourseCount(): int
    {
        return Course::where('status', true)->count();
    }

    /**
     * Generate course suggestions based on user's enrolled courses
     */
    private function generateSuggestions($user, $limit = 12, $filters = [])
    {
        $userEnrollmentData = $this->getUserEnrollmentData($user);
        $query = $this->buildSuggestionsQuery($userEnrollmentData, $filters);
        $this->applySorting($query, $filters['sort'] ?? 'rating');
        
        if (!empty($filters['offset'])) {
            $query->skip($filters['offset']);
        }
        
        $courses = $query->take($limit)->get();
        
        return $this->formatCourseData($courses, $userEnrollmentData['enrolled_course_ids']);
    }

    /**
     * Get user enrollment data efficiently
     */
    private function getUserEnrollmentData($user): array
    {
        $enrollments = $user->enrollments()
            ->with('course.category')
            ->get();
        
        return [
            'enrolled_categories' => $enrollments->pluck('course.category.id')
                ->filter()
                ->unique()
                ->toArray(),
            'enrolled_course_ids' => $enrollments->pluck('course_id')->toArray(),
        ];
    }

    /**
     * Build the main suggestions query
     */
    private function buildSuggestionsQuery(array $enrollmentData, array $filters)
    {
        $query = Course::with(['category', 'instructor'])
            ->withCount(['lectures', 'enrollments'])
            ->where('status', true)
            ->whereNotIn('id', $enrollmentData['enrolled_course_ids']);
        
        $this->applyFilters($query, $filters, $enrollmentData['enrolled_categories']);
        
        return $query;
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, array $filters, array $enrolledCategories): void
    {
        // Category filter
        if (!empty($filters['category'])) {
            $query->whereHas('category', fn($q) => $q->where('name', $filters['category']));
        } elseif (!empty($enrolledCategories)) {
            $query->whereHas('category', fn($q) => $q->whereIn('id', $enrolledCategories));
        }
        
        // Level filter
        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }
        
        // Price filter
        if (!empty($filters['price'])) {
            $query->where('price', $filters['price'] === 'free' ? 0 : '>', 0);
        }
    }

    /**
     * Apply sorting to the query
     */
    private function applySorting($query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'popular' => $query->orderBy('enrollments_count', 'desc'),
            default => $query->orderBy('rating', 'desc'),
        };
    }

    /**
     * Format course data for response
     */
    private function formatCourseData($courses, array $enrolledCourseIds)
    {
        return $courses->map(fn($course) => [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'description' => $course->description,
            'image' => $course->image,
            'price' => $course->price,
            'rating' => $course->rating,
            'level' => $course->level,
            'category' => $course->category->name ?? 'Uncategorized',
            'instructor' => $course->instructor->name,
            'lectures_count' => $course->lectures_count,
            'duration' => $course->duration,
            'is_enrolled' => in_array($course->id, $enrolledCourseIds),
            'is_free' => $course->price == 0,
        ]);
    }
}