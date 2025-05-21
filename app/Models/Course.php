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
        'user_id', // تم إضافته
        'category_id', // تم إضافته
        'price' // تم إضافته
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

    // إضافة دالة للتحقق من حالة الدورة
    public function isPublished(): bool
    {
        return $this->status === 'published';
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
}
