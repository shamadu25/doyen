<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_TECHNICIAN = 'technician';
    const ROLE_RECEPTIONIST = 'receptionist';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'phone',
        'date_of_birth',
        'hire_date',
        'hourly_rate',
        'commission_rate',
        'skills',
        'certifications',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'hourly_rate' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'skills' => 'array',
            'certifications' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function assignedJobs()
    {
        return $this->hasMany(JobCard::class, 'assigned_to');
    }

    public function schedules()
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    // Role checks
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager()
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isTechnician()
    {
        return $this->role === self::ROLE_TECHNICIAN;
    }

    public function isReceptionist()
    {
        return $this->role === self::ROLE_RECEPTIONIST;
    }

    public function hasRole(...$roles)
    {
        return in_array($this->role, $roles);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTechnicians($query)
    {
        return $query->where('role', self::ROLE_TECHNICIAN)->where('is_active', true);
    }

    public function scopeAvailableForDate($query, $date)
    {
        return $query->whereDoesntHave('schedules', function ($q) use ($date) {
            $q->forDate($date)->where('status', '!=', 'absent');
        });
    }

    // Helper methods
    public function getTodaySchedule()
    {
        return $this->schedules()->forDate(today())->first();
    }

    public function getCurrentWorkload()
    {
        return $this->assignedJobs()
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
    }

    public function getMonthlyCommissions($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
        
        return $this->commissions()
            ->whereYear('period_end', $year)
            ->whereMonth('period_end', $month)
            ->sum('commission_amount');
    }

    public function hasSkill($skill)
    {
        return in_array($skill, $this->skills ?? []);
    }

    public function hasCertification($certification)
    {
        return in_array($certification, $this->certifications ?? []);
    }

    public static function getAvailableTechnicians($skillRequired = null)
    {
        $query = self::technicians()->where('is_active', true);
        
        if ($skillRequired) {
            $query->whereJsonContains('skills', $skillRequired);
        }
        
        return $query->get()->sortBy(function ($technician) {
            return $technician->getCurrentWorkload();
        });
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
