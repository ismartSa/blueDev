<?php

namespace App\Http\Requests\course;

use Illuminate\Foundation\Http\FormRequest;

class CourseIndexRequest extends FormRequest
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
            'field' => ['in:title'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }
}
