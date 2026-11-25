<?php

namespace App\Models;

use App\Enums\Gender;
use App\Models\Concerns\HasRegisterDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Legal secretary licensing register. Code 014. */
class LegalSecretary extends Model
{
    use HasRegisterDocuments;

    protected $fillable = [
        'organization_id',
        'full_name',
        'age',
        'gender',
        'address',
        'license_number',
        'licensed_date_ec',
        'clients_served_male',
        'clients_served_female',
        'clients_served_total',
        'service_fee',
        'license_fee',
        'is_renewed_this_year',
        'served_by_expert',
        'created_by',
    ];

    protected $casts = [
        'age' => 'integer',
        'gender' => Gender::class,
        'clients_served_male' => 'integer',
        'clients_served_female' => 'integer',
        'clients_served_total' => 'integer',
        'service_fee' => 'decimal:2',
        'license_fee' => 'decimal:2',
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
