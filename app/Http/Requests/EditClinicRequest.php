<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditClinicRequest extends FormRequest
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
        $requiredString = 'required|string';
        return [
            'name' => $requiredString,
            'address' => $requiredString,
            'city' => $requiredString,
            'state' => $requiredString,
            'zipcode' => $requiredString
        ];
    }
}
