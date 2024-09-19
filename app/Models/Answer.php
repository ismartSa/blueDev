<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'answer_en',
        'answer_ar',
        'is_correct',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function translations()
    {
        return $this->hasMany(AnswerTranslation::class);
    }
}
