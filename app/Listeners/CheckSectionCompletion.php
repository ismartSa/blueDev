<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CheckSectionCompletion implements ShouldQueue
{
    use InteractsWithQueue;

  public function handle(LectureCompleted $event): void
    {
        $section = $event->progress->lecture->section;
        $userId = $event->progress->user_id;

        if ($section->isCompletedByUser($userId)) {
            event(new SectionCompleted($section, $userId));
        }
    }
}
