<?php

namespace App\Support;

use App\Models\PerformanceReport;

class PerformanceAlerts
{
    public static function notify(PerformanceReport $report): void
    {
        $report->loadMissing('user');

        SubmissionAlerts::notify(
            $report,
            $report->user?->displayName().' አፈጻጸም አቅርቧል',
            $report->period->label().' '.$report->period_number.' · '.$report->budget_year_ec,
            route('admin.performance.show', $report, false),
        );
    }
}
