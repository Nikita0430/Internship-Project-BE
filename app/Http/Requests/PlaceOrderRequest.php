<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $requiredStringValidation = 'required|string';
        $requiredIntegerValidation = 'required|integer';
        return [
            'clinic_id' => $requiredIntegerValidation,
            'email' => 'required|string|email',
            'injection_date' => 'required|date|after:today',
            'dog_name' => $requiredStringValidation,
            'dog_breed' => $requiredStringValidation,
            'dog_age' => $requiredIntegerValidation,
            'dog_weight' => 'required|numeric',
            'dog_gender' => 'required|in:male,female',
            'reactor_name' => $requiredStringValidation,
            'reactor_cycle_id' => $requiredIntegerValidation,
            'no_of_elbows' => $requiredIntegerValidation,
            'dosage_per_elbow' => 'required|numeric',
        ];
    }
}
