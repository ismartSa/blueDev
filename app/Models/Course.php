<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
