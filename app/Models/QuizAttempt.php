<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'started_at',
        'completed_at',
        'score',
        'answers',
        'time_taken',
        'passed',
        'total_questions',
        'correct_answers'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
        'passed' => 'boolean',
        'score' => 'float',
        'time_taken' => 'integer',
        'total_questions' => 'integer',
        'correct_answers' => 'integer'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    // Business Logic Methods
    public function calculateScore(): float
    {
        if (!$this->answers || $this->total_questions === 0) {
            return 0.0;
        }

        $correctCount = 0;
        $questions = $this->quiz->questions;

        foreach ($this->answers as $questionId => $selectedAnswerId) {
            $question = $questions->find($questionId);
            if ($question) {
                $correctAnswer = $question->answers()->where('is_correct', true)->first();
                if ($correctAnswer && $correctAnswer->id == $selectedAnswerId) {
                    $correctCount++;
                }
            }
        }

        $this->update([
            'correct_answers' => $correctCount,
            'score' => round(($correctCount / $this->total_questions) * 100, 2)
        ]);

        return $this->score;
    }

    public function markAsCompleted(): self
    {
        $this->update([
            'completed_at' => now(),
            'time_taken' => $this->getTimeTaken(),
            'passed' => $this->isPassing()
        ]);

        return $this;
    }

    public function getTimeTaken(): int
    {
        if (!$this->started_at) return 0;
        
        $endTime = $this->completed_at ?? now();
        return $this->started_at->diffInSeconds($endTime);
    }

    public function getFormattedTimeTaken(): string
    {
        $minutes = floor($this->time_taken / 60);
        $seconds = $this->time_taken % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function isPassing(): bool
    {
        return $this->score >= ($this->quiz->passing_score ?? 60);
    }

    public function getDetailedResults(): array
    {
        return [
            'score' => $this->score,
            'passed' => $this->passed,
            'correct_answers' => $this->correct_answers,
            'total_questions' => $this->total_questions,
            'time_taken' => $this->getFormattedTimeTaken(),
            'percentage' => round($this->score, 2) . '%',
            'passing_score' => $this->quiz->passing_score ?? 60
        ];
    }

    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }

    public function isInProgress(): bool
    {
        return !is_null($this->started_at) && is_null($this->completed_at);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeInProgress($query)
    {
        return $query->whereNotNull('started_at')
                    ->whereNull('completed_at');
    }

    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('passed', false);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
