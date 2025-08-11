<?php

namespace App\Http\Requests\Lecture;

use Illuminate\Foundation\Http\FormRequest;

class StoreLectureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'duration' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:1',
            'section_id' => 'required|exists:sections,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Lecture name is required',
            'title.required' => 'Lecture title is required',
            'section_id.required' => 'Section is required',
            'section_id.exists' => 'Selected section does not exist'
        ];
    }
}
