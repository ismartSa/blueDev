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
            'title.required' => 'عنوان الدورة مطلوب',
            'status.required' => 'حالة الدورة مطلوبة',
            'category_id.exists' => 'الفئة المحددة غير موجودة',
            'thumbnail.image' => 'يجب أن يكون الملف صورة',
            'thumbnail.max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت'
        ];
    }
}
