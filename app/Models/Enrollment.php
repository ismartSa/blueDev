<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Enrollment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_status',
        'enrollment_date',
        'completion_date',
        'progress_percentage',
        'current_lecture_id',
        'viewed_lectures',
        'completed_lectures',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enrollment_date' => 'datetime',
        'completion_date' => 'datetime',
        'progress_percentage' => 'integer',
        'viewed_lectures' => 'array',
        'completed_lectures' => 'array',
    ];

    /**
     * Get the user associated with this enrollment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course associated with this enrollment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Check if the enrollment is active.
     */
    public function isActive(): bool
    {
        return $this->enrollment_status === 'active';
    }

    /**
     * Check if the enrollment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->progress_percentage >= 100;
    }

    /**
     * Get the lectures that have been viewed for this enrollment.
     */
    public function viewedLectures(): BelongsToMany
    {
        return $this->belongsToMany(Lecture::class, 'lecture_user_progress', 'user_id', 'lecture_id')
                    ->wherePivot('completed', true)
                    ->withPivot('completed')
                    ->withTimestamps();
    }
}
