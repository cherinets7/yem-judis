<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationType;
use App\Enums\ReviewStatus;
use App\Enums\StaffCategory;
use App\Helpers\EthiopianDateHelper;
use App\Models\AnnualPlan;
use App\Models\Law;
use App\Models\Organization;
use App\Models\PerformanceReport;
use App\Models\ProsecutorWeeklyReport;
use App\Models\StaffProfile;
use App\Models\StrategicFocusData;
use App\Models\WeeklyReport;
use App\Support\OrganizationScope;
use App\Support\Roles;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $ids = OrganizationScope::ids();
        $budgetYear = EthiopianDateHelper::currentBudgetYear();

        $prosecutors = StaffProfile::query()
            ->whereIn('organization_id', $ids)
            ->where('staff_category', StaffCategory::Prosecutor)
            ->count();
        $servants = StaffProfile::query()
            ->whereIn('organization_id', $ids)
            ->where('staff_category', StaffCategory::PublicServant)
            ->count();

        $organizations = Organization::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Organization $organization) => $this->typeOrder($organization).$organization->amharicName())
            ->values();

        $plans = AnnualPlan::query()
            ->whereIn('organization_id', $ids)
            ->where('budget_year_ec', $budgetYear)
            ->get()
            ->groupBy(fn (AnnualPlan $plan) => (int) $plan->organization_id);
        $weeks = WeeklyReport::query()
            ->whereIn('organization_id', $ids)
            ->where('budget_year_ec', $budgetYear)
            ->get()
            ->groupBy(fn (WeeklyReport $report) => (int) $report->organization_id);
        $focus = StrategicFocusData::query()
            ->whereIn('organization_id', $ids)
            ->where('budget_year_ec', $budgetYear)
            ->get()
            ->groupBy(fn (StrategicFocusData $row) => (int) $row->organization_id);
        $prosecutorWeeks = ProsecutorWeeklyReport::query()
            ->whereIn('organization_id', $ids)
            ->where('budget_year_ec', $budgetYear)
            ->get()
            ->groupBy(fn (ProsecutorWeeklyReport $report) => (int) $report->organization_id);

        $matrix = $organizations->map(function (Organization $organization) use ($plans, $weeks, $focus, $prosecutorWeeks) {
            $weekly = $weeks->get($organization->id);
            $prosecutor = $prosecutorWeeks->get($organization->id);

            return [
                'name' => $organization->amharicName(),
                'type' => $organization->type?->label() ?? '',
                'annual' => $this->latestStatus($plans->get($organization->id)),
                'weekly' => $this->latestStatus($weekly),
                'weekly_count' => $weekly?->count() ?? 0,
                'strategic' => $this->latestStatus($focus->get($organization->id)),
                'prosecutor' => $this->latestStatus($prosecutor),
                'prosecutor_count' => $prosecutor?->count() ?? 0,
            ];
        });

        $user = auth()->user();
        $reviewer = Roles::allows($user, 'reviewers');
        $queues = [
            ['label' => 'አፈጻጸም', 'count' => $this->submitted(PerformanceReport::class, $ids)],
            ['label' => 'ሳምንታዊ', 'count' => $this->submitted(WeeklyReport::class, $ids)],
            ['label' => 'ዐቃቤ ህግ ሳምንታዊ', 'count' => $this->submitted(ProsecutorWeeklyReport::class, $ids)],
            ['label' => 'ዓመታዊ', 'count' => $this->submitted(AnnualPlan::class, $ids)],
            ['label' => 'ስትራቴጂክ', 'count' => $this->submitted(StrategicFocusData::class, $ids)],
        ];
        $performance = PerformanceReport::query()
            ->with(['user', 'organization'])
            ->when(
                $reviewer,
                fn ($query) => $query
                    ->whereIn('organization_id', $ids)
                    ->where('status', ReviewStatus::Submitted),
            )
            ->when(
                ! $reviewer,
                fn ($query) => $query->where('user_id', $user?->id),
            )
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', [
            'prosecutors' => $prosecutors,
            'servants' => $servants,
            'combined' => $prosecutors + $servants,
            'laws' => Law::query()->count(),
            'budgetYear' => $budgetYear,
            'matrix' => $matrix,
            'performance' => $performance,
            'performanceReviewer' => $reviewer,
            'queues' => $queues,
            'waitingTotal' => array_sum(array_column($queues, 'count')),
        ]);
    }

    public function zone(): View
    {
        $ids = OrganizationScope::ids();
        $zone = Organization::query()
            ->with(['workUnits', 'children.workUnits'])
            ->whereIn('id', $ids)
            ->where('type', OrganizationType::Zone)
            ->first();

        return view('dashboard.zone', [
            'zone' => $zone,
        ]);
    }

    public function woredas(): View
    {
        $offices = Organization::query()
            ->with('workUnits')
            ->whereIn('id', OrganizationScope::ids())
            ->whereIn('type', [OrganizationType::Woreda, OrganizationType::City])
            ->orderBy('name')
            ->get();

        return view('dashboard.woredas', [
            'offices' => $offices,
        ]);
    }

    /**
     * @param  class-string  $model
     * @param  list<int>  $ids
     */
    private function submitted(string $model, array $ids): int
    {
        return $model::query()
            ->whereIn('organization_id', $ids)
            ->where('status', ReviewStatus::Submitted)
            ->count();
    }

    private function typeOrder(Organization $organization): string
    {
        return match ($organization->type) {
            OrganizationType::Zone => '0',
            OrganizationType::City => '1',
            OrganizationType::Woreda => '2',
            default => '3',
        };
    }

    private function latestStatus(mixed $rows): string
    {
        if ($rows === null || $rows->isEmpty()) {
            return 'አልቀረበም';
        }

        $latest = $rows->sortByDesc(fn ($row) => $row->updated_at?->getTimestamp() ?? 0)->first();
        $status = $latest?->status;

        return $status instanceof ReviewStatus ? $status->label() : 'አልቀረበም';
    }
}
