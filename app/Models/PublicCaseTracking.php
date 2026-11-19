<?php

namespace App\Models;

use App\Enums\CourtLevel;
use App\Enums\FinalOutcome;
use App\Enums\PublicCaseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicCaseTracking extends Model
{
    protected $fillable = [
        'organization_id',
        'prosecutor_file_number',
        'court_file_number',
        'plaintiff_name',
        'defendant_name',
        'case_type',
        'court_level',
        'appointment_date_ec',
        'appointment_reason',
        'final_outcome',
        'is_published',
        'updated_by',
    ];

    protected $casts = [
        'case_type' => PublicCaseType::class,
        'court_level' => CourtLevel::class,
        'final_outcome' => FinalOutcome::class,
        'is_published' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
