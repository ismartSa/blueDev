<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'title',
        'name',
        'slug',
        'description',
        'body',
        'duration',
        'image',
        'status',
        'intro_video',
        'user_id', // Added
        'category_id', // Added
        'price', // Added
        'level', // Added for course difficulty level
        'language', // Added for course language
        'design_settings' // Added for design customization
    ];

    protected $casts = [
        'design_settings' => 'array'
    ];

    public function isFree()
    {
        return $this->price == 0;
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    // أضف هذه العلاقة
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    // إضافة علاقة المدرب (instructor)
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Mutator for setting the slug
    public function setSlugAttribute($value)
    {
        $slug = Str::slug($value); // Generate a slug from the course name
        $this->attributes['slug'] = $slug;
    }

    // Mutator for getting the slug
    public function getSlugAttribute($value)
    {
        return $value; // Simply return the stored slug
    }

    // إضافة علاقة التصنيف (category)
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Check if course is published/available
    public function isPublished(): bool
    {
        $rawStatus = $this->getRawOriginal('status') ?? $this->getAttributes()['status'] ?? $this->status;
        return is_bool($rawStatus) ? $rawStatus : ($rawStatus === 'published');
    }

    // Status accessor for display purposes
    public function getStatusAttribute($value)
    {
        if (is_bool($value)) {
            return $value ? 'Active' : 'Inactive';
        }
        
        return match($value) {
            'published' => 'Active',
            'draft' => 'Draft',
            'inactive' => 'Inactive',
            default => 'Draft'
        };
    }

    // إضافة دالة للحصول على مدة الدورة بشكل منسق
    public function formattedDuration(): string
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        return $hours > 0
            ? sprintf('%dh %02dm', $hours, $minutes)
            : sprintf('%dm', $minutes);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }

    // Get quizzes by domain
    public function getQuizzesByDomain($domain)
    {
        return $this->quizzes()->byDomain($domain)->get();
    }

    // Get quizzes by chapter
    public function getQuizzesByChapter($chapter)
    {
        return $this->quizzes()->byChapter($chapter)->get();
    }
}
