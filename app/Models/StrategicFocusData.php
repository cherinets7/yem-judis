<?php

namespace App\Models;

use App\Enums\Quarter;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategicFocusData extends Model
{
    protected $table = 'strategic_focus_data';

    protected $fillable = [
        'organization_id',
        'work_unit_id',
        'user_id',
        'budget_year_ec',
        'quarter',
        'focus_area',
        'target_value',
        'achieved_value',
        'narrative_summary',
        'status',
    ];

    protected $casts = [
        'quarter' => Quarter::class,
        'status' => ReviewStatus::class,
        'budget_year_ec' => 'integer',
        'target_value' => 'decimal:2',
        'achieved_value' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workUnit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class);
    }
}
