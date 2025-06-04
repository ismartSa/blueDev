<?php

namespace App\Http\Controllers\Course;

use Inertia\Inertia;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\CourseStoreRequest;
use App\Http\Requests\CourseUpdateRequest;

class CourseManagementController extends Controller
{
    public function index(Request $request)
    {
         // Get search and filter parameters
         $search = $request->get('search');
         $field = $request->get('field', 'created_at');
         $order = $request->get('order', 'desc');
         $perPage = $request->get('perPage', 10);

         // Validate sort field to prevent SQL injection
         $allowedFields = ['title', 'status', 'created_at', 'updated_at'];
         if (!in_array($field, $allowedFields)) {
             $field = 'created_at';
         }

         // Validate sort order
         $order = in_array($order, ['asc', 'desc']) ? $order : 'desc';

         // Validate per page
         $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

         // Build query
         $coursesQuery = Course::query()
             ->with(['category:id,name']) // Eager load category with specific fields
             ->select(['id', 'title', 'status', 'category_id', 'created_at', 'updated_at']);

         // Apply search filter
         if ($search) {
             $coursesQuery->where(function ($query) use ($search) {
                 $query->where('title', 'like', "%{$search}%")
                       ->orWhere('status', 'like', "%{$search}%")
                       ->orWhereHas('category', function ($q) use ($search) {
                           $q->where('name', 'like', "%{$search}%");
                       });
             });
         }

         // Apply sorting
         $coursesQuery->orderBy($field, $order);

         // Get paginated results
         $courses = $coursesQuery->paginate($perPage)
             ->withQueryString() // Preserve query parameters in pagination links
             ->through(function ($course) {
                 return [
                     'id' => $course->id,
                     'title' => $course->title,
                     'status' => ucfirst($course->status),
                     'category' => $course->category ? [
                         'id' => $course->category->id,
                         'name' => $course->category->name,
                     ] : null,
                     'created_at' => $course->created_at->format('Y-m-d H:i'),
                     'updated_at' => $course->updated_at->format('Y-m-d H:i'),
                 ];
             });

         // Get categories for filters (if needed)
         $categories = Category::select(['id', 'name'])
             ->orderBy('name')
             ->get();

         // Breadcrumbs
         $breadcrumbs = [
             ['name' => 'Dashboard', 'url' => route('dashboard')],
             ['name' => 'Courses', 'url' => null],
         ];

         return Inertia::render('Dashboard/Course/Index', [
             'title' => 'Courses Management',
             'courses' => $courses,
             'categories' => $categories,
             'filters' => [
                 'search' => $search,
                 'field' => $field,
                 'order' => $order,
             ],
             'perPage' => $perPage,
             'breadcrumbs' => $breadcrumbs,
             'stats' => [
                 'total' => Course::count(),
                 'active' => Course::where('status', 'active')->count(),
                 'inactive' => Course::where('status', 'inactive')->count(),
             ],
         ]);
    }

    public function create()
    {
        // ... existing create method code ...
    }

    public function store(CourseStoreRequest $request)
    {
        // ... existing store method code ...
    }

    public function update(Course $course, CourseUpdateRequest $request)
    {
        $validated = $request->validated();

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course->update($validated);

        return back()->with('success', 'Course updated successfully!');
    }

    public function delete(Course $course)
    {
        // ... existing delete method code ...
    }
}
