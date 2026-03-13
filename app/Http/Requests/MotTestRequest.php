<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MotTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => 'required|exists:vehicles,id',
            'job_card_id' => 'nullable|exists:job_cards,id',
            'test_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:test_date',
            'test_result' => 'nullable|in:booked,passed,failed,advisory,retest',
            'mileage' => 'nullable|integer|min:0',
            'advisories' => 'nullable|array',
            'advisories.*' => 'string|max:500',
            'failures' => 'nullable|array',
            'failures.*' => 'string|max:500',
            'notes' => 'nullable|string|max:2000',
            'tester_name' => 'nullable|string|max:255',
            'certificate' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
        ];
    }
}
