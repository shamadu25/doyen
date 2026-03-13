<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCardPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_card_id',
        'part_id',
        'part_number',
        'part_name',
        'quantity',
        'unit_cost',
        'unit_price',
        'discount',
        'vat_rate',
        'status',
        'notes',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
    ];

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
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
