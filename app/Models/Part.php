<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Part extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'part_number',
        'name',
        'description',
        'manufacturer',
        'category',
        'cost_price',
        'selling_price',
        'vat_rate',
        'stock_quantity',
        'minimum_stock',
        'supplier',
        'supplier_part_number',
        'location',
        'tecdoc_data',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'tecdoc_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function jobCardParts()
    {
        return $this->hasMany(JobCardPart::class);
    }

    public function needsReorder()
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }
}
