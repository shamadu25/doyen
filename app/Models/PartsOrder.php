<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartsOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'job_card_id',
        'user_id',
        'supplier',
        'supplier_order_reference',
        'status',
        'subtotal',
        'vat',
        'delivery_cost',
        'total',
        'delivery_method',
        'expected_delivery_date',
        'actual_delivery_date',
        'delivery_address',
        'tracking_number',
        'notes',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = 'PO-' . strtoupper(uniqid());
            }
        });
    }

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PartsOrderItem::class);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function markAsConfirmed($supplierReference = null)
    {
        $this->update([
            'status' => 'confirmed',
            'supplier_order_reference' => $supplierReference ?? $this->supplier_order_reference,
        ]);
    }

    public function markAsShipped($trackingNumber = null, $expectedDelivery = null)
    {
        $this->update([
            'status' => 'shipped',
            'tracking_number' => $trackingNumber ?? $this->tracking_number,
            'expected_delivery_date' => $expectedDelivery ?? $this->expected_delivery_date,
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'actual_delivery_date' => now(),
        ]);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}
