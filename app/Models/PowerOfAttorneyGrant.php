<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Power of attorney grant register. Code 008. */
class PowerOfAttorneyGrant extends Model
{
    protected $fillable = [
        'organization_id',
        'grantor_name',
        'grantor_address',
        'grantor_age',
        'grantor_gender',
        'poa_content',
        'registration_number',
        'granted_date_ec',
        'stamp_fee',
        'service_fee',
        'grantee_name',
        'grantee_address',
        'grantee_age',
        'grantee_gender',
        'approved_by_expert',
        'created_by',
    ];

    protected $casts = [
        'grantor_age' => 'integer',
        'grantor_gender' => Gender::class,
        'stamp_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'grantee_age' => 'integer',
        'grantee_gender' => Gender::class,
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function approvedByExpert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_expert');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
