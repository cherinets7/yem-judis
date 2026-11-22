<?php

namespace App\Models;

use App\Enums\Gender;
use App\Models\Concerns\HasAssignedExpert;
use App\Models\Concerns\HasRegisterDocuments;
use App\Models\Concerns\ProjectsToPublicCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Criminal appeal register. Code 003. */
class CriminalAppeal extends Model
{
    use HasAssignedExpert;
    use HasRegisterDocuments;
    use ProjectsToPublicCase;

    protected $fillable = [
        'organization_id',
        'expert_id',
        'prosecutor_appeal_number',
        'lower_court_file_number',
        'appellant_name',
        'respondent_name',
        'address',
        'age',
        'gender',
        'lower_court_decision',
        'appeal_filed_date_ec',
        'appellate_court_and_file_no',
        'assigned_prosecutor',
        'decision_date_and_outcome',
        'case_age',
        'created_by',
    ];

    protected $casts = [
        'age' => 'integer',
        'gender' => Gender::class,
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
