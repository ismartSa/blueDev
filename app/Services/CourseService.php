<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Category;
use App\Models\User;
use App\Exceptions\CourseException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

/**
 * Service class for course-related business logic
 * Handles course operations, filtering, and data processing
 */
class CourseService
{
    /**
     * Create a new course
     */
    public function create(array $data)
    {
        try {
            return Course::create($data);
        } catch (\Exception $e) {
            throw new CourseException('Failed to create course: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing course
     */
    public function update(Course $course, array $data)
    {
        try {
            // Handle thumbnail upload if present
            if (isset($data['thumbnail']) && $data['thumbnail']) {
                $data['thumbnail'] = $this->handleThumbnailUpload($data['thumbnail'], $course->thumbnail);
            } else {
                // Remove thumbnail from data if not provided to avoid overwriting existing one
                unset($data['thumbnail']);
            }

            // Generate slug if title changed
            if (isset($data['title']) && $data['title'] !== $course->title) {
                $data['slug'] = $this->generateUniqueSlug($data['title'], $course->id);
            }

            $course->update($data);
            return $course->fresh();
        } catch (\Exception $e) {
            throw new CourseException('Failed to update course: ' . $e->getMessage());
        }
    }

    /**
     * Get filtered and paginated courses
     */
    public function getFilteredCourses(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->buildBaseQuery(['category', 'instructor', 'enrollments']);
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);
        
        return $query->paginate($perPage);
    }

    /**
     * Get course statistics
     */
    public function getCourseStats(Course $course): array
    {
        return [
            'total_enrollments' => $course->enrollments()->count(),
            'total_lectures' => $course->lectures()->count(),
            'total_duration' => $course->lectures()->sum('duration'),
            'completion_rate' => $this->calculateCompletionRate($course),
            'average_rating' => $course->reviews()->avg('rating') ?? 0,
            'total_reviews' => $course->reviews()->count(),
        ];
    }

    /**
     * Calculate course completion rate
     */
    private function calculateCompletionRate(Course $course): float
    {
        $totalEnrollments = $course->enrollments()->count();
        if ($totalEnrollments === 0) return 0;

        $completedEnrollments = $course->enrollments()
            ->whereNotNull('completed_at')
            ->count();

        return round(($completedEnrollments / $totalEnrollments) * 100, 2);
    }

    /**
     * Get popular courses
     */
    public function getPopularCourses(int $limit = 6): Collection
    {
        return Course::with(['category', 'instructor'])
            ->withCount('enrollments')
            ->where('status', 'published')
            ->orderBy('enrollments_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get featured courses
     */
    public function getFeaturedCourses(int $limit = 6): Collection
    {
        return Course::with(['category', 'instructor'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recommended courses for user
     */
    public function getRecommendedCourses(User $user, int $limit = 6): Collection
    {
        // Optimize query by getting category IDs directly
        $enrolledCategories = $user->enrollments()
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->distinct()
            ->pluck('courses.category_id')
            ->filter();

        if ($enrolledCategories->isEmpty()) {
            return $this->getPopularCourses($limit);
        }

        // Get enrolled course IDs in a single query
        $enrolledCourseIds = $user->enrollments()->pluck('course_id');

        return $this->buildBaseQuery(['category', 'instructor'])
            ->whereIn('category_id', $enrolledCategories)
            ->whereNotIn('id', $enrolledCourseIds)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Format course data for API response
     */
    public function formatCourseData(Course $course, User $user = null): array
    {
        $data = $this->getBaseCourseData($course);
        $data['stats'] = $this->getCourseStats($course);

        if ($user) {
            $data = array_merge($data, $this->getUserSpecificData($course, $user));
        }

        return $data;
    }

    /**
     * Get base course data structure
     */
    private function getBaseCourseData(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'description' => $course->description,
            'image' => $course->image,
            'price' => $course->price,
            'level' => $course->level,
            'duration' => $course->duration,
            'status' => $course->status,
            'category' => $this->formatRelationData($course->category, ['id', 'name', 'slug']),
            'instructor' => $this->formatRelationData($course->instructor, ['id', 'name', 'avatar']),
        ];
    }

    /**
     * Format relation data dynamically
     */
    private function formatRelationData($relation, array $fields): array
    {
        return collect($fields)->mapWithKeys(fn($field) => [$field => $relation->{$field}])->toArray();
    }

    /**
     * Get user-specific course data
     */
    private function getUserSpecificData(Course $course, User $user): array
    {
        return [
            'user_enrollment' => $course->enrollments()->where('user_id', $user->id)->first(),
            'is_wishlisted' => $course->wishlists()->where('user_id', $user->id)->exists(),
        ];
    }

    /**
     * Handle thumbnail upload
     */
    private function handleThumbnailUpload(UploadedFile $file, ?string $oldThumbnail = null): string
    {
        try {
            // Delete old thumbnail if exists
            if ($oldThumbnail && Storage::disk('public')->exists($oldThumbnail)) {
                Storage::disk('public')->delete($oldThumbnail);
            }

            // Store new thumbnail
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('courses/thumbnails', $filename, 'public');
            
            return $path;
        } catch (\Exception $e) {
            throw new CourseException('Failed to upload thumbnail: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique slug for course
     */
    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Course::where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            if (!$query->exists()) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
     }

    /**
     * Format course data for display page
     */
    public function formatCourseForDisplay(Course $course): array
    {
        $user = Auth::user();
        
        return [
            'course' => $course,
            'statistics' => $this->getDisplayStatistics($course),
            'enrollmentStatus' => $this->getEnrollmentStatus($user, $course->id),
            'progress' => $user ? $this->getUserProgress($user, $course) : null,
            'meta' => $this->getCourseMeta($course),
        ];
    }

    /**
     * Get statistics for course display
     */
    private function getDisplayStatistics(Course $course): array
    {
        $sections = $course->sections;
        
        return [
            'lecturesCount' => $sections->sum(fn($section) => $section->lectures->count()),
            'totalDuration' => $sections->flatMap(fn($section) => $section->lectures)->sum('duration'),
            'enrollmentsCount' => $course->enrollments()->count(),
            'completionRate' => $this->calculateCompletionRate($course),
        ];
    }

    /**
     * Get course meta data
     */
    private function getCourseMeta(Course $course): array
    {
        return [
            'title' => $course->title,
            'description' => $course->description,
        ];
    }

    /**
     * Get courses for public exploration
     */
    public function getPublicCourses(array $filters = []): LengthAwarePaginator
    {
        $query = $this->buildBaseQuery(['category', 'instructor'])
            ->withCount(['lectures as lessons_count', 'enrollments']);
        
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters, 'public');
        
        $courses = $query->paginate(12)->appends(request()->query());
        
        // Add user enrollment status if user is authenticated
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user) {
                $enrolledCourseIds = $user->enrollments()->pluck('course_id')->toArray();
                
                // Modify each course item in the paginated collection
                foreach ($courses->items() as $course) {
                    $course->user_enrolled = in_array($course->id, $enrolledCourseIds);
                }
            }
        } else {
            // For guest users, set user_enrolled to false
            foreach ($courses->items() as $course) {
                $course->user_enrolled = false;
            }
        }
        
        return $courses;
    }

    /**
     * Get platform statistics
     */
    public function getPlatformStats(): array
    {
        return [
            'totalCourses' => Course::where('status', 'published')->count(),
            'totalStudents' => User::whereHas('enrollments')->count(),
            'totalInstructors' => User::whereHas('courses')->count(),
        ];
    }

    /**
     * Duplicate a course
     */
    public function duplicate(Course $course): Course
    {
        $newCourse = $course->replicate();
        $newCourse->title = $course->title . ' - Copy';
        $newCourse->slug = $course->slug . '-copy-' . time();
        $newCourse->status = 'draft';
        $newCourse->save();

        // Duplicate sections and lectures
        foreach ($course->sections as $section) {
            $newSection = $section->replicate();
            $newSection->course_id = $newCourse->id;
            $newSection->save();

            foreach ($section->lectures as $lecture) {
                $newLecture = $lecture->replicate();
                $newLecture->section_id = $newSection->id;
                $newLecture->course_id = $newCourse->id;
                $newLecture->save();
            }
        }

        return $newCourse;
    }

    /**
     * Get enrollment status for user
     */
    private function getEnrollmentStatus($user, $courseId): array
    {
        if (!$user) return ['enrolled' => false, 'status' => null];

        $enrollment = $user->enrollments()
            ->where('course_id', $courseId)
            ->first();

        return [
            'enrolled' => (bool) $enrollment,
            'status' => $enrollment?->status,
            'enrollmentDate' => $enrollment?->created_at
        ];
    }

    /**
     * Get user progress for course
     */
    private function getUserProgress($user, Course $course): ?array
    {
        if (!$user) return null;

        $totalLectures = $course->sections->sum(fn($section) => $section->lectures->count());
        $completedLectures = $user->lectureProgress()
            ->whereHas('lecture', fn($query) => $query->where('course_id', $course->id))
            ->where('completed', true)
            ->count();

        return [
            'completedLectures' => $completedLectures,
            'totalLectures' => $totalLectures,
            'percentage' => $totalLectures > 0 ? round(($completedLectures / $totalLectures) * 100, 1) : 0
        ];
    }

    /**
     * Search courses with advanced filters
     */
    public function searchCourses(string $query, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $searchQuery = Course::with(['category', 'instructor'])
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhereHas('category', function ($categoryQuery) use ($query) {
                      $categoryQuery->where('name', 'like', '%' . $query . '%');
                  })
                  ->orWhereHas('instructor', function ($instructorQuery) use ($query) {
                      $instructorQuery->where('name', 'like', '%' . $query . '%');
                  });
            });

        // Apply additional filters
        $filters['search'] = $query;
        return $this->applyFilters($searchQuery, $filters)->paginate($perPage);
    }

    /**
     * Build base query for courses
     */
    private function buildBaseQuery(array $relations = [])
    {
        return Course::with($relations)->where('status', 'published');
    }

    /**
     * Apply filters to query with dynamic filter mapping
     */
    private function applyFilters($query, array $filters): void
    {
        $filterMap = [
            'category' => fn($value) => $query->where('category_id', $value),
            'level' => fn($value) => $query->where('level', $value),
            'price_type' => fn($value) => $this->applyPriceFilter($query, $value),
            'price' => fn($value) => $this->applyPriceFilter($query, $value),
            'search' => fn($value) => $this->applySearchFilter($query, $value),
            'price_range' => fn($value) => $this->applyPriceRangeFilter($query, $value),
        ];

        foreach ($filters as $key => $value) {
            if (!empty($value) && isset($filterMap[$key])) {
                $filterMap[$key]($value);
            }
        }
    }

    /**
     * Apply price filter
     */
    private function applyPriceFilter($query, string $priceType): void
    {
        switch ($priceType) {
            case 'free':
                $query->where('price', 0);
                break;
            case 'paid':
                $query->where('price', '>', 0);
                break;
        }
    }

    /**
     * Apply search filter
     */
    private function applySearchFilter($query, string $searchTerm): void
    {
        $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Apply price range filter
     */
    private function applyPriceRangeFilter($query, string $priceRange): void
    {
        [$min, $max] = explode('-', $priceRange);
        $query->whereBetween('price', [(float)$min, (float)$max]);
    }

    /**
     * Apply sorting with dynamic sort mapping
     */
    private function applySorting($query, array $filters, string $context = 'default'): void
    {
        if ($context === 'public') {
            $this->applyPublicSorting($query, $filters['sort'] ?? 'latest');
        } else {
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);
        }
    }

    /**
     * Apply public sorting options
     */
    private function applyPublicSorting($query, string $sortType): void
    {
        $sortMap = [
            'popular' => fn() => $query->orderBy('enrollments_count', 'desc'),
            'title' => fn() => $query->orderBy('title', 'asc'),
            'price_low' => fn() => $query->orderBy('price', 'asc'),
            'price_high' => fn() => $query->orderBy('price', 'desc'),
            'latest' => fn() => $query->latest(),
        ];

        ($sortMap[$sortType] ?? $sortMap['latest'])();
    }
}
