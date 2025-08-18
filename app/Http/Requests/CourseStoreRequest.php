<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseStoreRequest extends FormRequest
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
            'duration' => 'nullable|numeric|min:0.5', // Changed to nullable and numeric
            'price' => 'nullable|numeric|min:0',      // Changed to nullable
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
            'title.required' => 'عنوان الدورة مطلوب',
            'description.required' => 'وصف الدورة مطلوب',
            'duration.required' => 'مدة الدورة مطلوبة',
            'price.required' => 'سعر الدورة مطلوب',
            'status.required' => 'حالة الدورة مطلوبة'
        ];
    }
}
