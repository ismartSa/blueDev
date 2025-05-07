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
            'description' => 'required|string',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,draft',
            'intro_video' => 'nullable|url'
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
