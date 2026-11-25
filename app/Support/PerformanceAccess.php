<?php

namespace App\Support;

use App\Models\PerformanceReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PerformanceAccess
{
    public static function visible(Request $request, PerformanceReport $report): PerformanceReport
    {
        if ($report->user_id === $request->user()->id) {
            return $report;
        }

        abort_unless(
            Roles::allows($request->user(), 'reviewers')
                && in_array((int) $report->organization_id, OrganizationScope::ids(), true),
            404,
        );

        return $report;
    }

    public static function query(Request $request): Builder
    {
        $user = $request->user();
        $reviewer = Roles::allows($user, 'reviewers');

        return PerformanceReport::query()
            ->with(['user', 'organization', 'reviewer'])
            ->when($reviewer, fn ($query) => $query->whereIn('organization_id', OrganizationScope::ids()))
            ->when(! $reviewer, fn ($query) => $query->where('user_id', $user->id))
            ->latest();
    }
}
