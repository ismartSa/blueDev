<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|numeric|min:0.5', // Made nullable and allow decimals
            'price' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,draft,inactive',
            'category_id' => 'nullable|exists:categories,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'language' => 'nullable|in:en,ar,fr,es'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Course title is required.',
            'status.required' => 'Course status is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'thumbnail.image' => 'The file must be an image.',
            'thumbnail.max' => 'The image size must be less than 2MB.',
            'duration.numeric' => 'Duration must be a valid number.',
            'duration.min' => 'Duration must be at least 0.5 hours.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'status.in' => 'Status must be active, draft, or inactive.',
            'level.in' => 'Level must be beginner, intermediate, or advanced.',
            'language.in' => 'Language must be en, ar, fr, or es.'
        ];
    }
}
