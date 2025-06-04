<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'duration' => $this->duration,
            'price' => $this->price,
            'status' => $this->status,
            'intro_video' => $this->intro_video,
            'slug' => $this->slug,
            'image' => $this->image,
            'thumbnail' => $this->thumbnail,
            'category' => $this->whenLoaded('category', function() {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'instructor' => $this->whenLoaded('instructor', function() {
                return [
                    'id' => $this->instructor->id,
                    'name' => $this->instructor->name,
                ];
            }),
            'sections' => $this->whenLoaded('sections', function() {
                return SectionResource::collection($this->sections)->additional([
                    'total_count' => $this->sections->count(),
                    'has_lectures' => $this->sections->contains(function($section) {
                        return $section->lectures && $section->lectures->isNotEmpty();
                    })
                ]);
            }, []),
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null
        ];
    }
}
