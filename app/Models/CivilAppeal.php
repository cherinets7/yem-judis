<?php

namespace App\Models;

use App\Enums\AppellantType;
use App\Models\Concerns\HasAssignedExpert;
use App\Models\Concerns\ProjectsToPublicCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Civil appeal register. Code 006. */
class CivilAppeal extends Model
{
    use HasAssignedExpert;
    use ProjectsToPublicCase;

    protected $fillable = [
        'organization_id',
        'expert_id',
        'appellant_type',
        'appellant_address',
        'respondent_address',
        'age',
        'prosecutor_appeal_number',
        'appeal_filed_date_ec',
        'lower_court_file_number',
        'appealed_court_name',
        'appellate_court_file_number',
        'appeal_money_amount',
        'appeal_land_size',
        'appellate_decision_and_date',
        'case_age',
        'created_by',
    ];

    protected $casts = [
        'appellant_type' => AppellantType::class,
        'age' => 'integer',
        'appeal_money_amount' => 'decimal:2',
        'appeal_land_size' => 'decimal:4',
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
