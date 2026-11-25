<?php

namespace App\Models;

use App\Enums\Gender;
use App\Models\Concerns\HasRegisterDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Attorney licensing register. Code 013. */
class Attorney extends Model
{
    use HasRegisterDocuments;

    protected $fillable = [
        'organization_id',
        'full_name',
        'gender',
        'age',
        'address',
        'licensing_body',
        'license_number',
        'licensed_date_ec',
        'license_fee',
        'service_fee',
        'pro_bono_clients_male',
        'pro_bono_clients_female',
        'pro_bono_clients_total',
        'pro_bono_hours',
        'service_year_ec',
        'is_renewed_this_year',
        'served_by_expert',
        'created_by',
    ];

    protected $casts = [
        'gender' => Gender::class,
        'age' => 'integer',
        'license_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'pro_bono_clients_male' => 'integer',
        'pro_bono_clients_female' => 'integer',
        'pro_bono_clients_total' => 'integer',
        'pro_bono_hours' => 'integer',
        'service_year_ec' => 'integer',
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
