<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    /**
     * الخصائص التي يمكن تعيينها بشكل جماعي
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_status',
        'enrollment_date',
        'completion_date',
        'progress_percentage',
    ];

    /**
     * الخصائص التي يجب تحويلها
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enrollment_date' => 'datetime',
        'completion_date' => 'datetime',
        'progress_percentage' => 'integer',
    ];

    /**
     * علاقة المستخدم المرتبط بهذا التسجيل
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة الدورة المرتبطة بهذا التسجيل
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * التحقق مما إذا كان التسجيل نشطًا
     */
    public function isActive(): bool
    {
        return $this->enrollment_status === 'active';
    }

    /**
     * التحقق مما إذا كان التسجيل مكتملاً
     */
    public function isCompleted(): bool
    {
        return $this->progress_percentage >= 100;
    }
}
