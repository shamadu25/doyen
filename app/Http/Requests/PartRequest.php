<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'part_number' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:100',
        ];
    }
}
