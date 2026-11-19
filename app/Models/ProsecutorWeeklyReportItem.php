<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProsecutorWeeklyReportItem extends Model
{
    protected $fillable = [
        'prosecutor_weekly_report_id',
        'section_code',
        'row_label',
        'metrics',
    ];

    protected $casts = [
        'metrics' => 'array',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(ProsecutorWeeklyReport::class, 'prosecutor_weekly_report_id');
    }
}
