<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCardService extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_card_id',
        'service_id',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'vat_rate',
        'notes',
        'completed',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'completed' => 'boolean',
    ];

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getLineTotalAttribute()
    {
        return ($this->unit_price * $this->quantity) - $this->discount;
    }

    public function getVatAmountAttribute()
    {
        return $this->line_total * ($this->vat_rate / 100);
    }
}
