<?php

namespace App\Models;

use App\Enums\ProsecutorReportType;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProsecutorWeeklyReport extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'report_type',
        'budget_year_ec',
        'week_number',
        'period_covered_ec',
        'status',
        'reviewed_by',
    ];

    protected $casts = [
        'report_type' => ProsecutorReportType::class,
        'budget_year_ec' => 'integer',
        'week_number' => 'integer',
        'status' => ReviewStatus::class,
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

    public function items(): HasMany
    {
        return $this->hasMany(ProsecutorWeeklyReportItem::class);
    }
}
