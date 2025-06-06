<?php

namespace App\Http\Requests\Role;
use app\Models\Role;

use Illuminate\Foundation\Http\FormRequest;

class RoleUpdateRequest extends FormRequest
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
            'name' => 'required|unique:roles,name,' . $this->role->id,
            'permissions' => ['required', 'array'],
        ];
    }
}
