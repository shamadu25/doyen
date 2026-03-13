<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'postcode',
        'country',
        'customer_type',
        'company_name',
        'vat_number',
        'notes',
        'is_active',
        'password',
        'password_reset_token',
        'email_notifications',
        'sms_notifications',
        'appointment_reminders',
        'service_reminders',
        'mot_reminders',
        'marketing_emails',
    ];

    protected $hidden = [
        'password',
        'password_reset_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'appointment_reminders' => 'boolean',
        'service_reminders' => 'boolean',
        'mot_reminders' => 'boolean',
        'marketing_emails' => 'boolean',
    ];

    protected $appends = ['name', 'full_name'];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function jobCards()
    {
        return $this->hasMany(JobCard::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
