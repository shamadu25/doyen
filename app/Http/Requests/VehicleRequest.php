<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'registration_number' => ['required', 'string', 'max:10', 'regex:/^[A-Z]{2}[0-9]{2}\s?[A-Z]{3}$|^[A-Z][0-9]{1,3}\s?[A-Z]{3}$|^[A-Z]{3}\s?[0-9]{1,3}[A-Z]$|^[0-9]{1,4}\s?[A-Z]{1,3}$/i'],
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'variant' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vin' => 'nullable|string|max:17',
            'fuel_type' => 'nullable|in:petrol,diesel,electric,hybrid,lpg,other',
            'transmission' => 'nullable|in:manual,automatic',
            'engine_size' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:50',
            'mileage' => 'nullable|integer|min:0',
            'mot_due_date' => 'nullable|date',
            'tax_due_date' => 'nullable|date',
            'service_due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'registration_number.regex' => 'Please enter a valid UK registration number.',
        ];
    }
}
