<?php

namespace App\Http\Requests\course;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CourseStoreRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'body' => 'required|string',
            'duration' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif', // Validation for image upload
            'intro_video' => 'nullable|string',


        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title field is required.',
            'name.required' => 'The name field is required.',
            'description.required' => 'The description field is required.',
            'body.required' => 'The body field is required.',
            'duration.required' => 'The duration field is required.',
            'image.image' => 'The image must be a valid image file.',
            'image.mimes' => 'The image must be a jpeg, png, jpg, or gif file.',
        ];
    }
}
