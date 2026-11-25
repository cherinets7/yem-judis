<?php

namespace App\Models;

use App\Enums\CourtLevel;
use App\Enums\Gender;
use App\Models\Concerns\HasAssignedExpert;
use App\Models\Concerns\HasRegisterDocuments;
use App\Models\Concerns\ProjectsToPublicCase;
use App\Models\Concerns\SearchableRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Criminal case register. Code 001 woreda court, code 002 high court. */
class CriminalCase extends Model
{
    use HasAssignedExpert;
    use HasRegisterDocuments;
    use ProjectsToPublicCase;
    use SearchableRegister;

    protected $fillable = [
        'organization_id',
        'expert_id',
        'court_level',
        'prosecutor_file_number',
        'police_file_number_and_pages',
        'complainant_and_police_date',
        'submitted_to_justice_date',
        'defendant_names',
        'defendant_address',
        'defendant_gender',
        'defendant_age',
        'police_investigated_crime',
        'victim_and_age',
        'prosecutor_charged_crime',
        'filed_in_court_date',
        'court_file_number',
        'court_decision_type',
        'closed_indefinitely',
        'case_age',
        'assigned_prosecutor',
        'witnesses_count_and_gender',
        'appointment_date_ec',
        'appointment_reason',
        'created_by',
    ];

    protected $casts = [
        'court_level' => CourtLevel::class,
        'defendant_gender' => Gender::class,
        'defendant_age' => 'integer',
        'closed_indefinitely' => 'boolean',
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
