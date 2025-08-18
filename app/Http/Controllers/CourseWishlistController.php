<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Handles course wishlist operations including adding/removing courses
 * from wishlist and displaying user's wishlist.
 */
class CourseWishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Toggle course wishlist status
     */
    public function toggle(Request $request, $courseId)
    {
        $user = Auth::user();
        $course = Course::findOrFail($courseId);

        $existingWishlist = Wishlist::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();

        if ($existingWishlist) {
            // Remove from wishlist
            $existingWishlist->delete();
            $message = 'Course removed from wishlist';
            $isWishlisted = false;
        } else {
            // Add to wishlist
            Wishlist::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
            ]);
            $message = 'Course added to wishlist';
            $isWishlisted = true;
        }

        // Return JSON response for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_wishlisted' => $isWishlisted
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Display user's wishlist
     */
    public function index()
    {
        $user = Auth::user();

        $wishlistCourses = Wishlist::where('user_id', $user->id)
            ->with(['course.category', 'course.instructor'])
            ->get()
            ->map(function ($wishlist) {
                $course = $wishlist->course;
                return [
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
                    'duration' => $course->duration,
                    'is_free' => $course->price == 0,
                    'wishlist_id' => $wishlist->id,
                    'added_at' => $wishlist->created_at,
                ];
            });

        return Inertia::render('Courses/Wishlist', [
            'courses' => $wishlistCourses,
        ]);
    }

    /**
     * Remove course from wishlist
     */
    public function destroy($courseId)
    {
        $user = Auth::user();
        
        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $message = 'Course removed from wishlist';
        } else {
            $message = 'Course not found in wishlist';
        }

        return back()->with('success', $message);
    }

    /**
     * Add course to wishlist
     */
    public function store(Request $request, $courseId)
    {
        $user = Auth::user();
        $course = Course::findOrFail($courseId);

        $existingWishlist = Wishlist::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();

        if ($existingWishlist) {
            return back()->with('info', 'Course is already in your wishlist');
        }

        Wishlist::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
        ]);

        return back()->with('success', 'Course added to wishlist');
    }

    /**
     * Check if course is in user's wishlist
     */
    public function check($courseId)
    {
        $user = Auth::user();
        
        $isWishlisted = Wishlist::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->exists();

        return response()->json([
            'is_wishlisted' => $isWishlisted
        ]);
    }

    /**
     * Get wishlist count for user
     */
    public function count()
    {
        $user = Auth::user();
        $count = Wishlist::where('user_id', $user->id)->count();

        return response()->json([
            'count' => $count
        ]);
    }
}