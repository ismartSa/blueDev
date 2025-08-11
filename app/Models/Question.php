<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_title_en',
        'question_title_ar',
        'question_type_id',
        'correct_answers_required',
        'feedback',
        'image',
        'demo'
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function translations()
    {
        return $this->hasMany(QuestionTranslation::class);
    }
}
