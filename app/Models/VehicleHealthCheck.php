<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleHealthCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_card_id',
        'vehicle_id',
        'inspector_id',
        'inspection_area',
        'condition',
        'notes',
        'recommendation',
        'photo_path',
        'requires_attention',
        'customer_approved',
        'customer_approved_at',
    ];

    protected $casts = [
        'requires_attention' => 'boolean',
        'customer_approved' => 'boolean',
        'customer_approved_at' => 'datetime',
    ];

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Get badge color for condition
     */
    public function getConditionBadgeAttribute()
    {
        return [
            'green' => 'bg-green-100 text-green-800',
            'amber' => 'bg-yellow-100 text-yellow-800',
            'red' => 'bg-red-100 text-red-800',
        ][$this->condition] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get condition label
     */
    public function getConditionLabelAttribute()
    {
        return [
            'green' => 'Good Condition',
            'amber' => 'Attention Soon',
            'red' => 'Urgent Action Required',
        ][$this->condition] ?? 'Unknown';
    }
}

