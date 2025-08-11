<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'answer_en',
        'answer_ar',
        'is_correct',
    ];

    /**
     * Get the question that owns the answer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get all translations for the answer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(\App\Models\AnswerTranslation::class);
    }

    /**
     * Get the answer in a specific locale.
     *
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslation(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        if ($locale === 'en') {
            return $this->answer_en;
        }

        if ($locale === 'ar') {
            return $this->answer_ar;
        }

        return $this->translations()
            ->where('locale', $locale)
            ->value('text');
    }
}
