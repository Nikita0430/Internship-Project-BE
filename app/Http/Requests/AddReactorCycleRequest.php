<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddReactorCycleRequest extends FormRequest
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
        return [
            'cycle_name' => 'required|string',
            'reactor_name' => 'required|string',
            'mass' => 'required|numeric',
            'target_start_date' => 'required|date|after_or_equal:today',
            'expiration_date' => 'required|date|after:target_start_date',
        ];
    }
}