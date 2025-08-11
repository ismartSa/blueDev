<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentRequest extends FormRequest
{
    /**
     * تحديد ما إذا كان المستخدم مصرح له بتقديم هذا الطلب
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * الحصول على قواعد التحقق التي تنطبق على الطلب
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
        ];
    }

    /**
     * رسائل الخطأ المخصصة
     */
    public function messages(): array
    {
        return [
            'course_id.required' => 'معرف الدورة مطلوب',
            'course_id.exists' => 'الدورة المحددة غير موجودة',
        ];
    }
}
