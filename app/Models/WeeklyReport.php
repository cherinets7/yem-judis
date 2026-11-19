<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReport extends Model
{
    protected $fillable = [
        'organization_id',
        'work_unit_id',
        'user_id',
        'budget_year_ec',
        'week_number',
        'week_range_ec',
        'planned_activities',
        'accomplished_activities',
        'challenges',
        'attachment_path',
        'status',
        'reviewed_by',
    ];

    protected $casts = [
        'status' => ReviewStatus::class,
        'budget_year_ec' => 'integer',
        'week_number' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function workUnit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class);
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
