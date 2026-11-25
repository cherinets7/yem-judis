<?php

namespace App\Models;

use App\Models\Concerns\HasRegisterDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Legal studies and dissemination register. Code 011. */
class LegalStudiesDissemination extends Model
{
    use HasRegisterDocuments;

    protected $fillable = [
        'organization_id',
        'budget_year_ec',
        'needs_assessment_topic',
        'manuals_prepared',
        'brochures_prepared',
        'awareness_platforms_count',
        'reached_kebeles_cities',
        'media_awareness_male',
        'media_awareness_female',
        'media_awareness_total',
        'reached_schools',
        'legal_advice_institutions',
        'legal_advice_individuals',
        'legal_advice_total',
        'total_citizens_male',
        'total_citizens_female',
        'total_citizens_sum',
        'collected_proclamations',
        'collected_regulations',
        'collected_directives',
        'collected_total',
        'disseminated_proclamations',
        'disseminated_regulations',
        'disseminated_directives',
        'disseminated_total',
        'drafted_laws_count',
        'law_enforcement_study_count',
        'impact_effective_count',
        'impact_ineffective_count',
        'created_by',
    ];

    protected $casts = [
        'budget_year_ec' => 'integer',
        'manuals_prepared' => 'integer',
        'brochures_prepared' => 'integer',
        'awareness_platforms_count' => 'integer',
        'reached_kebeles_cities' => 'integer',
        'media_awareness_male' => 'integer',
        'media_awareness_female' => 'integer',
        'media_awareness_total' => 'integer',
        'reached_schools' => 'integer',
        'legal_advice_institutions' => 'integer',
        'legal_advice_individuals' => 'integer',
        'legal_advice_total' => 'integer',
        'total_citizens_male' => 'integer',
        'total_citizens_female' => 'integer',
        'total_citizens_sum' => 'integer',
        'collected_proclamations' => 'integer',
        'collected_regulations' => 'integer',
        'collected_directives' => 'integer',
        'collected_total' => 'integer',
        'disseminated_proclamations' => 'integer',
        'disseminated_regulations' => 'integer',
        'disseminated_directives' => 'integer',
        'disseminated_total' => 'integer',
        'drafted_laws_count' => 'integer',
        'law_enforcement_study_count' => 'integer',
        'impact_effective_count' => 'integer',
        'impact_ineffective_count' => 'integer',
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
