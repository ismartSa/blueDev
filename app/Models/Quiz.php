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
        'section_id', // تأكد من إضافة هذا السطر
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // إضافة العلاقة مع الدورة
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
