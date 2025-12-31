<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:users,phone_number|regex:/^[0-9]+$/',
            'email' => 'nullable|email|unique:users,email',
            'date_of_birth' => 'nullable|date',
            'account_type' => 'required|in:RENTER,OWNER,ADMIN',
            'password' => 'required|string|min:6',
            'avatar_url' => 'nullable|string',
            'identity_docomunt_url' => 'nullable|string',
            'status' => 'nullable|in:PENDING,APPROVED,REJECTED',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'الاسم الأول مطلوب',
            'last_name.required' => 'الاسم الأخير مطلوب',
            'phone_number.required' => 'رقم الهاتف مطلوب',
            'phone_number.unique' => 'رقم الهاتف مستخدم بالفعل',
            'phone_number.regex' => 'رقم الهاتف يجب أن يحتوي على أرقام فقط',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'account_type.required' => 'نوع الحساب مطلوب',
            'account_type.in' => 'نوع الحساب غير صحيح',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 6 أحرف',
        ];
    }
}

