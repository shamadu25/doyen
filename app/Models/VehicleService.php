<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'job_card_id',
        'service_date',
        'mileage',
        'service_type',
        'work_carried_out',
        'next_service_date',
        'next_service_mileage',
    ];

    protected $casts = [
        'service_date' => 'date',
        'next_service_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }
}
