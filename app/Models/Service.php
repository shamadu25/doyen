<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'default_price',
        'cost_price',
        'estimated_duration_minutes',
        'vat_rate',
        'is_active',
        'requires_booking',
        'is_approved',
        'show_on_website',
        'website_description',
        'icon',
        'sort_order',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'requires_booking' => 'boolean',
        'is_approved' => 'boolean',
        'show_on_website' => 'boolean',
    ];

    /**
     * Scope: only services that are active, approved, and visible on website
     */
    public function scopeWebsiteVisible($query)
    {
        return $query->where('is_active', true)
                     ->where('is_approved', true)
                     ->where('show_on_website', true)
                     ->orderBy('sort_order')
                     ->orderBy('name');
    }

    /**
     * Scope: active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function jobCardServices()
    {
        return $this->hasMany(JobCardService::class);
    }
}
