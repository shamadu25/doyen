<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'assigned_to' => 'nullable|exists:users,id',
            'appointment_type' => 'required|in:mot,service,repair,diagnosis',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'description' => 'nullable|string|max:2000',
            'customer_notes' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:2000',
        ];
    }
}
