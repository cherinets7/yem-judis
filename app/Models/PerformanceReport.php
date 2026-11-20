<?php

namespace App\Models;

use App\Enums\PerformancePeriod;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReport extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'period',
        'budget_year_ec',
        'period_number',
        'period_label',
        'planned',
        'accomplished',
        'challenges',
        'status',
        'reviewed_by',
        'review_note',
        'submitted_at',
    ];

    protected $casts = [
        'period' => PerformancePeriod::class,
        'budget_year_ec' => 'integer',
        'period_number' => 'integer',
        'status' => ReviewStatus::class,
        'submitted_at' => 'datetime',
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

    public function isOpen(): bool
    {
        return in_array($this->status, [ReviewStatus::Draft, ReviewStatus::Rejected], true);
    }
}
