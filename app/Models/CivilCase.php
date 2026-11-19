<?php

namespace App\Models;

use App\Enums\CourtLevel;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Civil case register. Code 004 high court, code 005 woreda court. */
class CivilCase extends Model
{
    protected $fillable = [
        'organization_id',
        'court_level',
        'plaintiff_name',
        'defendant_name',
        'plaintiff_address',
        'defendant_address',
        'gender',
        'age',
        'prosecutor_case_number',
        'negotiated_recovered_asset',
        'filed_date_ec',
        'claim_money_amount',
        'claim_land_size',
        'assigned_prosecutor',
        'court_file_number',
        'decision_date_and_type',
        'case_age',
        'witness_male',
        'witness_female',
        'witness_total',
        'created_by',
    ];

    protected $casts = [
        'court_level' => CourtLevel::class,
        'gender' => Gender::class,
        'age' => 'integer',
        'claim_money_amount' => 'decimal:2',
        'claim_land_size' => 'decimal:4',
        'witness_male' => 'integer',
        'witness_female' => 'integer',
        'witness_total' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
