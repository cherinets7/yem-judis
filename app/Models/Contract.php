<?php

namespace App\Models;

use App\Enums\Gender;
use App\Models\Concerns\HasRegisterDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Contract registration register. Code 010. */
class Contract extends Model
{
    use HasRegisterDocuments;

    protected $fillable = [
        'organization_id',
        'giver_name',
        'giver_address',
        'giver_age',
        'giver_gender',
        'receiver_name',
        'receiver_address',
        'receiver_age',
        'receiver_gender',
        'contract_summary',
        'registration_number',
        'registered_date_ec',
        'service_fee',
        'approved_by_expert',
        'created_by',
    ];

    protected $casts = [
        'giver_age' => 'integer',
        'giver_gender' => Gender::class,
        'receiver_age' => 'integer',
        'receiver_gender' => Gender::class,
        'service_fee' => 'decimal:2',
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
