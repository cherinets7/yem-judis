<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Power of attorney revocation register. Code 009. */
class PowerOfAttorneyRevocation extends Model
{
    protected $fillable = [
        'organization_id',
        'revoker_name',
        'revoker_address',
        'revoker_age',
        'revoker_gender',
        'previous_poa_ref_and_date',
        'revocation_reg_number',
        'revoked_date_ec',
        'stamp_fee',
        'service_fee',
        'revoked_from_name',
        'revoked_from_address',
        'revoked_from_age',
        'revoked_from_gender',
        'approved_by_expert',
        'created_by',
    ];

    protected $casts = [
        'revoker_age' => 'integer',
        'revoker_gender' => Gender::class,
        'stamp_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'revoked_from_age' => 'integer',
        'revoked_from_gender' => Gender::class,
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
