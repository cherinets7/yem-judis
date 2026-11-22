<?php

namespace App\Notifications;

use App\Models\PerformanceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PerformanceSubmitted extends Notification
{
    use Queueable;

    public function __construct(private PerformanceReport $report) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->report->loadMissing('user');

        return [
            'title' => $this->report->user?->displayName().' አፈጻጸም አቅርቧል',
            'body' => $this->report->period->label().' '.$this->report->period_number.' · '.$this->report->budget_year_ec,
            'url' => route('admin.performance.show', $this->report, false),
        ];
    }
}
