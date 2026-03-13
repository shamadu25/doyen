<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'part_id', 'type', 'quantity', 'reference_type', 'reference_id',
        'cost_price', 'notes', 'user_id',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public static function recordIn(Part $part, int $quantity, ?string $notes = null, $reference = null): self
    {
        $transaction = static::create([
            'part_id' => $part->id,
            'type' => 'in',
            'quantity' => $quantity,
            'cost_price' => $part->cost_price,
            'notes' => $notes,
            'user_id' => auth()->id(),
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);

        $part->increment('stock_quantity', $quantity);

        return $transaction;
    }

    public static function recordOut(Part $part, int $quantity, ?string $notes = null, $reference = null): self
    {
        $transaction = static::create([
            'part_id' => $part->id,
            'type' => 'out',
            'quantity' => $quantity,
            'cost_price' => $part->cost_price,
            'notes' => $notes,
            'user_id' => auth()->id(),
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);

        $part->decrement('stock_quantity', $quantity);

        return $transaction;
    }
}
