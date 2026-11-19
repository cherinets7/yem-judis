<?php

namespace App\Models;

use App\Enums\OrganizationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'type',
        'parent_id',
    ];

    protected $casts = [
        'type' => OrganizationType::class,
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function workUnits(): HasMany
    {
        return $this->hasMany(WorkUnit::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function annualPlans(): HasMany
    {
        return $this->hasMany(AnnualPlan::class);
    }

    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class);
    }

    public function strategicFocusData(): HasMany
    {
        return $this->hasMany(StrategicFocusData::class);
    }

    public function staffProfiles(): HasMany
    {
        return $this->hasMany(StaffProfile::class);
    }

    public function communityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function criminalCases(): HasMany
    {
        return $this->hasMany(CriminalCase::class);
    }

    public function criminalAppeals(): HasMany
    {
        return $this->hasMany(CriminalAppeal::class);
    }

    public function civilCases(): HasMany
    {
        return $this->hasMany(CivilCase::class);
    }

    public function civilAppeals(): HasMany
    {
        return $this->hasMany(CivilAppeal::class);
    }

    public function civilExecutions(): HasMany
    {
        return $this->hasMany(CivilExecution::class);
    }

    public function powerOfAttorneyGrants(): HasMany
    {
        return $this->hasMany(PowerOfAttorneyGrant::class);
    }

    public function powerOfAttorneyRevocations(): HasMany
    {
        return $this->hasMany(PowerOfAttorneyRevocation::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function legalStudiesDisseminations(): HasMany
    {
        return $this->hasMany(LegalStudiesDissemination::class);
    }

    public function civicAssociations(): HasMany
    {
        return $this->hasMany(CivicAssociation::class);
    }

    public function attorneys(): HasMany
    {
        return $this->hasMany(Attorney::class);
    }

    public function legalSecretaries(): HasMany
    {
        return $this->hasMany(LegalSecretary::class);
    }

    public function prosecutorWeeklyReports(): HasMany
    {
        return $this->hasMany(ProsecutorWeeklyReport::class);
    }

    public function publicCaseTrackings(): HasMany
    {
        return $this->hasMany(PublicCaseTracking::class);
    }
}
