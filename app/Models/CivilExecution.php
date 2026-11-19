<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Civil execution register. Code 007. */
class CivilExecution extends Model
{
    protected $fillable = [
        'organization_id',
        'execution_plaintiff',
        'plaintiff_address',
        'execution_defendant',
        'defendant_address',
        'age',
        'gender',
        'prosecutor_file_number',
        'executing_court',
        'court_file_number',
        'requested_money_amount',
        'requested_land_size',
        'court_decision_date_and_summary',
        'recovered_asset_amount',
        'unrecovered_asset_amount',
        'created_by',
    ];

    protected $casts = [
        'age' => 'integer',
        'gender' => Gender::class,
        'requested_money_amount' => 'decimal:2',
        'requested_land_size' => 'decimal:4',
        'recovered_asset_amount' => 'decimal:2',
        'unrecovered_asset_amount' => 'decimal:2',
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
