<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobCardRequest extends FormRequest
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
            'appointment_id' => 'nullable|exists:appointments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'mileage_in' => 'nullable|integer|min:0',
            'customer_complaint' => 'nullable|string|max:2000',
            'work_required' => 'nullable|string|max:2000',
            'technician_notes' => 'nullable|string|max:2000',
        ];
    }
}
