<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
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
        $rules = [
            'quantity' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'absolute' => 'nullable|boolean',
        ];

        // If not an absolute update, enforce that at least one of the unit fields is present.
        if (! $this->boolean('absolute')) {
            $rules['quantity'] .= '|required_without_all:weight,volume';
            $rules['weight'] .= '|required_without_all:quantity,volume';
            $rules['volume'] .= '|required_without_all:quantity,weight';
        }

        return $rules;
    }
}
