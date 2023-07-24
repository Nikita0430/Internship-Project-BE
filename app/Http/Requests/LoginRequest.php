<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * @author growexx
     * @return boolean
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @author growexx
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|regex:/^[a-zA-Z0-9.]+@[a-zA-Z0-9]+\.[a-zA-Z0-9.]+$/',
            'password' => 'required|min:6'
        ];
    }

    /**
     * Get the messages that should be sent on validation errors
     *
     * @author growexx
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'Email is required',
            'password.required' => 'Password is required',
            'email.email' => 'Email is invalid',
            'password.min' => 'Password is too short',
        ];
    }
}
