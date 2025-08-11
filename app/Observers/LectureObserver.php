<?php

namespace App\Observers;

use App\Models\Lecture;
use Illuminate\Support\Str;

class LectureObserver
{
    /**
     * Handle lecture slug generation
     *
     * @param Lecture $lecture
     * @return void
     */
    private function handleSlug(Lecture $lecture): void
    {
        // Generate slug from lecture title if title exists
        if ($lecture->title) {
            $baseSlug = Str::slug($lecture->title);
            $slug = $baseSlug;
            $counter = 1;
            
            // Check for existing slugs and make unique
            while (Lecture::where('slug', $slug)->where('id', '!=', $lecture->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $lecture->slug = $slug;
            $lecture->saveQuietly(); // Save changes without triggering observer again
        }
    }

    /**
     * معالجة حدث إنشاء المحاضرة
     *
     * @param Lecture $lecture
     * @return void
     */
    public function created(Lecture $lecture): void
    {
        $this->handleSlug($lecture);
    }

    /**
     * معالجة حدث تحديث المحاضرة
     *
     * @param Lecture $lecture
     * @return void
     */
    public function updated(Lecture $lecture): void
    {
        // تحديث slug فقط إذا تغير العنوان
        if ($lecture->isDirty('title')) {
            $this->handleSlug($lecture);
        }
    }

    /**
     * معالجة حدث حذف المحاضرة
     *
     * @param Lecture $lecture
     * @return void
     */
    public function deleted(Lecture $lecture): void
    {
        // يمكن إضافة منطق لتنظيف أي بيانات مرتبطة بالمحاضرة
        // مثل حذف ملفات الفيديو أو تحديث سجلات التقدم
    }

    /**
     * معالجة حدث استعادة المحاضرة
     *
     * @param Lecture $lecture
     * @return void
     */
    public function restored(Lecture $lecture): void
    {
        // يمكن إضافة منطق لاستعادة أي بيانات مرتبطة بالمحاضرة
    }

    /**
     * معالجة حدث الحذف النهائي للمحاضرة
     *
     * @param Lecture $lecture
     * @return void
     */
    public function forceDeleted(Lecture $lecture): void
    {
        // يمكن إضافة منطق للحذف النهائي لأي بيانات مرتبطة بالمحاضرة
    }
}
