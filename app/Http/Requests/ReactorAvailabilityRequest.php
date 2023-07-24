<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReactorAvailabilityRequest extends FormRequest
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
            'reactor_name' => 'required|string',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:2000,2100'
        ];
    }
}
