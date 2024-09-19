<?php

namespace App\Observers;

use App\Models\Lecture;
use Illuminate\Support\Str;

class LectureObserver
{
    /**
     * Handle the Lecture "created" event.
     */
    public function created(Lecture $lecture): void
    {
        $lecture->slug = Str::slug($lecture->title);
    }

    /**
     * Handle the Lecture "updated" event.
     */
    public function updated(Lecture $lecture): void
    {
        $lecture->slug = Str::slug($lecture->title);
    }

    /**
     * Handle the Lecture "deleted" event.
     */
    public function deleted(Lecture $lecture): void
    {
        //
    }

    /**
     * Handle the Lecture "restored" event.
     */
    public function restored(Lecture $lecture): void
    {
        //
    }

    /**
     * Handle the Lecture "force deleted" event.
     */
    public function forceDeleted(Lecture $lecture): void
    {
        //
    }
}
