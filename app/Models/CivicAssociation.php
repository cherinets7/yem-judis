<?php

namespace App\Models;

use App\Models\Concerns\HasRegisterDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Civic association register. Code 012. */
class CivicAssociation extends Model
{
    use HasRegisterDocuments;

    protected $fillable = [
        'organization_id',
        'association_name',
        'address',
        'association_type',
        'members_male',
        'members_female',
        'members_total',
        'capital_amount',
        'registration_number',
        'certificate_issued_date_ec',
        'registration_fee',
        'service_fee',
        'is_renewed_this_year',
        'served_by_expert',
        'created_by',
    ];

    protected $casts = [
        'members_male' => 'integer',
        'members_female' => 'integer',
        'members_total' => 'integer',
        'capital_amount' => 'decimal:2',
        'registration_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'is_renewed_this_year' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function servedByExpert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by_expert');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
