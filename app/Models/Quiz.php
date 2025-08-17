<?php

namespace App\Models;

use App\Traits\HasCommonAttributes;
use App\Traits\HasFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes, HasCommonAttributes, HasFilters {
        HasCommonAttributes::getStatusField insteadof HasFilters;
        HasCommonAttributes::getActiveField insteadof HasFilters;
    }

    protected $fillable = [
        'title',
        'description',
        'course_id',
        'lecture_id',
        'time_limit',
        'max_attempts',
        'passing_score',
        'is_active',
        'instructions',
        'show_results',
        'randomize_questions',
        'randomize_answers',
        'slug'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_results' => 'boolean',
        'randomize_questions' => 'boolean',
        'randomize_answers' => 'boolean',
        'time_limit' => 'integer',
        'max_attempts' => 'integer',
        'passing_score' => 'decimal:2'
    ];

    // Trait configuration
    protected $slugSource = 'title';
    protected $updateSlugOnChange = true;
    protected $searchable = ['title', 'description', 'course.title', 'lecture.title'];
    protected $sortable = ['id', 'title', 'created_at', 'updated_at', 'time_limit', 'max_attempts', 'passing_score'];
    protected $statusField = 'is_active';
    protected $activeField = 'is_active';
    protected $defaultActiveState = true;
    
    protected $statusClasses = [
        true => 'badge-success',
        false => 'badge-secondary',
        1 => 'badge-success',
        0 => 'badge-secondary'
    ];
    
    protected $statusLabels = [
        true => 'Active',
        false => 'Inactive',
        1 => 'Active',
        0 => 'Inactive'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
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

    /**
     * Override route key name to use ID instead of slug
     * This ensures compatibility with existing frontend routes
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
