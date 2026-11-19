<?php

namespace App\Models;

use App\Enums\PlanDocumentType;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnualPlan extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'budget_year_ec',
        'title',
        'document_type',
        'file_path',
        'summary',
        'status',
        'reviewed_by',
        'review_note',
    ];

    protected $casts = [
        'document_type' => PlanDocumentType::class,
        'status' => ReviewStatus::class,
        'budget_year_ec' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
