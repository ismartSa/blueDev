<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any courses.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view courses') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the course.
     */
    public function view(User $user, Course $course)
    {
        return $user->hasPermissionTo('view courses') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create courses.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create courses') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course)
    {
        // User can edit if they are the course instructor or have admin/edit permissions
        return $course->user_id === $user->id || 
               $user->hasPermissionTo('edit courses') || 
               $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the course.
     */
    public function delete(User $user, Course $course)
    {
        // User can delete if they are the course instructor or have admin/delete permissions
        return $course->user_id === $user->id || 
               $user->hasPermissionTo('delete courses') || 
               $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the course.
     */
    public function restore(User $user, Course $course)
    {
        return $user->hasPermissionTo('restore courses') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the course.
     */
    public function forceDelete(User $user, Course $course)
    {
        return $user->hasRole('admin');
    }
}