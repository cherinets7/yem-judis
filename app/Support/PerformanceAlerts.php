<?php

namespace App\Support;

use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\PerformanceReport;
use App\Models\User;
use App\Notifications\PerformanceSubmitted;
use Illuminate\Database\Eloquent\Collection;

class PerformanceAlerts
{
    public static function notify(PerformanceReport $report): void
    {
        if ($report->status !== ReviewStatus::Submitted) {
            return;
        }

        foreach (self::reviewers($report) as $reviewer) {
            $reviewer->notify(new PerformanceSubmitted($report));
        }
    }

    /**
     * @return Collection<int, User>
     */
    public static function reviewers(PerformanceReport $report): Collection
    {
        $report->loadMissing('organization');
        $organization = $report->organization;

        if ($organization === null) {
            return new Collection;
        }

        $ids = array_values(array_unique(array_filter([
            (int) $organization->id,
            $organization->parent_id ? (int) $organization->parent_id : null,
        ])));

        return User::query()
            ->whereIn('organization_id', $ids)
            ->whereIn('role', [UserRole::ZoneHead->value, UserRole::OfficeHead->value])
            ->where('is_active', true)
            ->where('id', '!=', $report->user_id)
            ->get();
    }
}
