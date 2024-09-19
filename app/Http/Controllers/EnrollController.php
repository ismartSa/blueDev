<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enroll;
use Illuminate\Http\Request;
use App\Http\Requests\StoreEnrollRequest;
use App\Http\Requests\UpdateEnrollRequest;

class EnrollController extends Controller
{


    public function enroll(Request $request, $courseId) {
        $course = Course::findOrFail($courseId); // Fetch the course
        $user = auth()->user(); // Get the authenticated user

        // Check if the user is already enrolled
        $existingEnrollment = $user->enrollments()->where('course_id', $courseId)->first();

        if ($existingEnrollment) {
            return redirect()->back()->with('info', 'You are already enrolled in this course.');
        }

        // Determine the enrollment status based on the course price
        $status = $course->isFree() ? 'confirmed' : 'pending';

        // Create the enrollment
        $enrollment = $user->enrollments()->create([
            'course_id' => $course->id,
            'enrollment_status' => $status,
        ]);

        if ($status === 'confirmed') {
            return redirect()->back()->with('success', 'You are now enrolled in this free course!');
        }

        // For paid courses, redirect to payment process (to be implemented)
        return redirect()->route('payment', ['course' => $course->id]);
    }

    public function checkEnrollment($courseId)
{
    $user = auth()->user();
    $isEnrolled = $user->enrollments()->where('course_id', $courseId)->exists();

    return response()->json(['isEnrolled' => $isEnrolled]);
}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEnrollRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Enroll $enroll)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enroll $enroll)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnrollRequest $request, Enroll $enroll)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enroll $enroll)
    {
        //
    }
}
