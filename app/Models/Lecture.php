<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{

    use HasFactory;

    protected $fillable = ['name', 'title', 'description', 'video_url', 'duration', 'order', 'slug', 'course_id', 'section_id'];


    public function section()
{
    return $this->belongsTo(Section::class);
}

public function usersProgress()
{
    return $this->hasMany(LectureUserProgress::class);
}
}
