<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:card,cash,bank_transfer,stripe,cheque',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
