<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LectureUserProgress extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'lecture_id',
        'completed',
        'progress_percentage',
        'last_position',
        'completed_at',
        'time_spent'
    ];

    protected $casts = [
        'completed' => 'boolean',
        'progress_percentage' => 'float',
        'last_position' => 'integer',
        'completed_at' => 'datetime',
        'time_spent' => 'integer'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    // Business Logic Methods
    public function markAsCompleted(): self
    {
        $this->update([
            'completed' => true,
            'progress_percentage' => 100.0,
            'completed_at' => now()
        ]);
        
        return $this;
    }

    public function updateProgress(float $percentage, int $position = null): self
    {
        $data = [
            'progress_percentage' => min(100.0, max(0.0, $percentage)),
            'last_position' => $position ?? $this->last_position
        ];
        
        // Auto-complete if progress reaches 100%
        if ($percentage >= 100.0) {
            $data['completed'] = true;
            $data['completed_at'] = now();
        }
        
        $this->update($data);
        return $this;
    }

    public function addTimeSpent(int $seconds): self
    {
        $this->increment('time_spent', $seconds);
        return $this;
    }

    public function getFormattedTimeSpent(): string
    {
        $minutes = floor($this->time_spent / 60);
        $seconds = $this->time_spent % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function isInProgress(): bool
    {
        return $this->progress_percentage > 0 && !$this->completed;
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopeInProgress($query)
    {
        return $query->where('progress_percentage', '>', 0)
                    ->where('completed', false);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
