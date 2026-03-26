<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quote_number',
        'customer_id',
        'vehicle_id',
        'appointment_id',
        'quote_date',
        'valid_until',
        'validity_days',
        'status',
        'review_token',
        'description',
        'notes',
        'terms',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'discount_percentage',
        'discount_amount',
        'approved_at',
        'declined_at',
        'converted_at',
        'converted_to_job_card_id',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'declined_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (empty($quote->quote_number)) {
                $quote->quote_number = static::generateQuoteNumber();
            }
            if (empty($quote->quote_date)) {
                $quote->quote_date = Carbon::today();
            }
            if (empty($quote->valid_until)) {
                $quote->valid_until = Carbon::today()->addDays($quote->validity_days ?? 30);
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class, 'converted_to_job_card_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Generate and assign a secure review token.
     */
    public function generateReviewToken(): string
    {
        $token = \Illuminate\Support\Str::random(48);
        $this->update(['review_token' => $token]);
        return $token;
    }

    /**
     * Generate unique quote number
     */
    public static function generateQuoteNumber()
    {
        $year = date('Y');
        $prefix = 'QTE';
        
        $lastQuote = static::where('quote_number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('quote_number')
            ->first();

        if ($lastQuote) {
            $lastNumber = (int) substr($lastQuote->quote_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $year, $newNumber);
    }

    /**
     * Calculate totals, respecting per-item VAT rates and tax-exempt lines.
     */
    public function calculateTotals()
    {
        $items = $this->items;
        $subtotal = $items->sum('total_price'); // net ex-VAT (qty × unit_price)

        $discountAmount = $this->discount_percentage > 0
            ? round($subtotal * ($this->discount_percentage / 100), 2)
            : 0;

        // Apply discount proportionally across all items
        $discountRatio = $subtotal > 0 ? (1 - ($discountAmount / $subtotal)) : 1;

        // Sum VAT per item, honouring each item's own vat_rate / tax_exempt flag
        $vatAmount = $items->sum(function ($item) use ($discountRatio) {
            if ($item->tax_exempt) {
                return 0;
            }
            $itemRate = (float) ($item->vat_rate ?? $this->vat_rate ?? 20);
            $net = (float) $item->total_price * $discountRatio;
            return round($net * ($itemRate / 100), 2);
        });

        $total = ($subtotal - $discountAmount) + $vatAmount;

        $this->update([
            'subtotal'        => round($subtotal, 2),
            'discount_amount' => $discountAmount,
            'vat_amount'      => round($vatAmount, 2),
            'total_amount'    => round($total, 2),
        ]);
    }

    /**
     * Check if quote is expired
     */
    public function isExpired()
    {
        return $this->valid_until < Carbon::today();
    }

    /**
     * Approve quote
     */
    public function approve()
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Decline quote
     */
    public function decline()
    {
        $this->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);
    }

    /**
     * Convert quote to job card
     */
    public function convertToJobCard()
    {
        if ($this->status !== 'approved') {
            return null;
        }

        $jobCard = JobCard::create([
            'customer_id' => $this->customer_id,
            'vehicle_id' => $this->vehicle_id,
            'date_in' => Carbon::today(),
            'status' => 'open',
            'priority' => 'normal',
            'description' => $this->description,
            'notes' => "Converted from Quote: {$this->quote_number}\n" . $this->notes,
        ]);

        // Copy items to job card
        foreach ($this->items as $item) {
            if ($item->item_type === 'service' && $item->service_id) {
                $jobCard->services()->create([
                    'service_id' => $item->service_id,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'cost' => 0,
                ]);
            } elseif ($item->item_type === 'part' && $item->part_id) {
                $jobCard->parts()->create([
                    'part_id' => $item->part_id,
                    'quantity' => $item->quantity,
                    'cost_price' => 0,
                    'selling_price' => $item->unit_price,
                ]);
            }
        }

        $jobCard->calculateTotals();

        $this->update([
            'status' => 'converted',
            'converted_at' => now(),
            'converted_to_job_card_id' => $jobCard->id,
        ]);

        return $jobCard;
    }
}
