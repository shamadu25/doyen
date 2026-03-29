<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:2000',
            'customer_type' => 'nullable|in:individual,business',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already being used by another customer.',
        ];
    }
}
