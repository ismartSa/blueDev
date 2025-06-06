<?php

namespace App\Http\Requests\Permission;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PermissionUpdateRequest extends FormRequest
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
       // return [
           // 'name' => 'required|unique:permissions,name,' . $this->permission->id,
       // ];
       return [
        'name' => [
            'required',
            Rule::unique('permissions', 'name')->ignore(
                $this->route('permission')?->id // الحل الأكثر أمانًا
            ),
        ],
    ];
    }
}
