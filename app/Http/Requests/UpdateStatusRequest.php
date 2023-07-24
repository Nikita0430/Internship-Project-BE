<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    private $orderMap = [
        'pending' => 0,
        'confirmed' => 1,
        'shipped' => 2,
        'out for delivery' => 3,
        'delivered' => 4,
        'cancelled' => 5
    ];

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
            'status' => 'required|in:'.implode(',', array_keys($this->orderMap))
        ];
    }
}
