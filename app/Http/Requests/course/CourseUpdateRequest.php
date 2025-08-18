<?php

namespace App\Http\Requests\course;

use Illuminate\Foundation\Http\FormRequest;

class CourseUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price'       => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'category_id' => ['required', 'exists:categories,id'],
            'thumbnail'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'status'      => ['nullable', 'in:draft,published,archived'],
        ];
    }
}
