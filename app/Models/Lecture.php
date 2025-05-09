<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecture extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'description',
        'video_url',
        'duration',
        'order',
        'slug',
        'course_id',
        'section_id'
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function usersProgress(): HasMany
    {
        return $this->hasMany(LectureUserProgress::class);
    }
}
