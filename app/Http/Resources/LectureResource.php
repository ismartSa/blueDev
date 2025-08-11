<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LectureResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'video_url' => $this->video_url,
            'duration' => $this->duration,
            'section_id' => $this->section_id,
            'course_id' => $this->course_id,
            'order' => $this->order,
            'completed' => $this->whenLoaded('progress', function() {
                if (!auth()->check()) return false;
                $user = auth()->user();

                return $this->progress
                    ->where('user_id', $user->id)
                    ->first()?->completed ?? false;
            }, false),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
