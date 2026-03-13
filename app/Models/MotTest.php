<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MotTest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'job_card_id',
        'test_number',
        'test_date',
        'expiry_date',
        'mileage',
        'test_result',
        'test_class',
        'advisories',
        'failures',
        'dvsa_data',
        'notes',
        'certificate_path',
    ];

    protected $casts = [
        'test_date' => 'datetime',
        'expiry_date' => 'date',
        'advisories' => 'array',
        'failures' => 'array',
        'dvsa_data' => 'array',
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
