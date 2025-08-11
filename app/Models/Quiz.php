<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'time_limit',
        'passing_score',
        'is_active',
        'course_id',
        'section_id',
        'domain', // New field for organizing quizzes
        'chapter', // New field for chapter organization
        'quiz_type', // e.g., 'practice', 'final', 'chapter_test'
        'order', // For ordering quizzes within a domain/chapter
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    // Scope for filtering by domain
    public function scopeByDomain($query, $domain)
    {
        return $query->where('domain', $domain);
    }

    // Scope for filtering by chapter
    public function scopeByChapter($query, $chapter)
    {
        return $query->where('chapter', $chapter);
    }
}
