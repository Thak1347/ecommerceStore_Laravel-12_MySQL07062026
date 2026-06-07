<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed|different:current_password',
            'new_password_confirmation' => 'required|string|min:6',
            
        ];
    }
    /**
     * Get custom message for validator errors.
     */

    public function messages(): array 
    {
        return [
            'current_password.required' => 'Current password is required.',
            'current_password.min' => 'Current password must be at least 6 characters.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 6 characters.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            'new_password.different' => 'New password must be different from current password.',
            'new_password_confirmation.required' => 'Please confirm your new password.',
        ];

    }

    /**
     * Get the validation rules after validation.
     */

    public function withValidator($validator)
    {
        $validator->after(function ($validator){
            // Additional custom validation can be added here
            $user = auth()->user();
            
            if ($this->current_password && !\Hash::check($this->current_password, $user->password)) {
                $validator->errors()->add('current_password', 'The current password is incorrect.');
            }
        });
    }
}
