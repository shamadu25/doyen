<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartsOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'parts_order_id',
        'part_number',
        'supplier_part_number',
        'part_name',
        'description',
        'manufacturer',
        'quantity',
        'unit_price',
        'discount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function partsOrder()
    {
        return $this->belongsTo(PartsOrder::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->line_total = ($item->unit_price * $item->quantity) - $item->discount;
        });
    }
}
