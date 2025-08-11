<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'order',
        'course_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class)->orderBy('order');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    // Business Logic Methods
    public function getTotalDuration(): int
    {
        return $this->lectures()->sum('duration') ?? 0;
    }

    public function getCompletionPercentage(int $userId): float
    {
        $totalLectures = $this->lectures()->count();
        if ($totalLectures === 0) return 100.0;
        
        $completedLectures = $this->lectures()
            ->whereHas('usersProgress', function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('completed', true);
            })->count();
            
        return round(($completedLectures / $totalLectures) * 100, 2);
    }

    public function isCompletedByUser(int $userId): bool
    {
        return $this->getCompletionPercentage($userId) === 100.0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
