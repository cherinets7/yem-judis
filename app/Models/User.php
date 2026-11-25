<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'organization_id',
        'work_unit_id',
        'role',
        'is_active',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'is_active' => 'boolean',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function displayName(): string
    {
        $name = preg_replace('/\s*\([^)]*\)\s*$/u', '', $this->name);

        return trim(is_string($name) ? $name : $this->name);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function workUnit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class);
    }

    public function annualPlans(): HasMany
    {
        return $this->hasMany(AnnualPlan::class);
    }

    public function reviewedAnnualPlans(): HasMany
    {
        return $this->hasMany(AnnualPlan::class, 'reviewed_by');
    }

    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class);
    }

    public function reviewedWeeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class, 'reviewed_by');
    }

    public function strategicFocusData(): HasMany
    {
        return $this->hasMany(StrategicFocusData::class);
    }

    public function communityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function uploadedLaws(): HasMany
    {
        return $this->hasMany(Law::class, 'uploaded_by');
    }

    public function prosecutorWeeklyReports(): HasMany
    {
        return $this->hasMany(ProsecutorWeeklyReport::class);
    }

    public function reviewedProsecutorWeeklyReports(): HasMany
    {
        return $this->hasMany(ProsecutorWeeklyReport::class, 'reviewed_by');
    }

    public function staffProfilesCreated(): HasMany
    {
        return $this->hasMany(StaffProfile::class, 'created_by');
    }

    public function staffProfilesFilled(): HasMany
    {
        return $this->hasMany(StaffProfile::class, 'filled_by_expert');
    }

    public function performanceReports(): HasMany
    {
        return $this->hasMany(PerformanceReport::class);
    }
}
