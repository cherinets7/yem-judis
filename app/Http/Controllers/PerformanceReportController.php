<?php

namespace App\Http\Controllers;

use App\Enums\PerformancePeriod;
use App\Enums\ReviewStatus;
use App\Helpers\EthiopianDateHelper;
use App\Models\PerformanceReport;
use App\Support\AuditLogger;
use App\Support\OrganizationScope;
use App\Support\PerformanceAlerts;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PerformanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $period = PerformancePeriod::tryFrom($request->string('period')->toString());
        $status = ReviewStatus::tryFrom($request->string('status')->toString());
        $user = $request->user();
        $reviewer = Roles::allows($user, 'reviewers');

        $reports = PerformanceReport::query()
            ->with(['user', 'organization', 'reviewer'])
            ->when(! $reviewer || $request->boolean('mine'), fn ($query) => $query->where('user_id', $user->id))
            ->when($reviewer && ! $request->boolean('mine'), fn ($query) => $query->whereIn('organization_id', OrganizationScope::ids()))
            ->when($period, fn ($query) => $query->where('period', $period))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('performance.index', [
            'reports' => $reports,
            'period' => $period,
            'status' => $status,
            'mine' => $request->boolean('mine'),
            'reviewer' => $reviewer,
            'waiting' => $reviewer
                ? PerformanceReport::query()
                    ->whereIn('organization_id', OrganizationScope::ids())
                    ->where('status', ReviewStatus::Submitted)
                    ->where('user_id', '!=', $user->id)
                    ->count()
                : 0,
        ]);
    }

    public function create(Request $request): View
    {
        $period = PerformancePeriod::tryFrom($request->string('period')->toString()) ?? PerformancePeriod::Weekly;

        return view('performance.form', [
            'report' => null,
            'period' => $period,
            'budgetYear' => EthiopianDateHelper::currentBudgetYear(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $report = PerformanceReport::query()->create($this->payload($request));
        PerformanceAlerts::notify($report);
        AuditLogger::record($request->user(), $report, 'created', $report->status === ReviewStatus::Submitted ? 'አፈጻጸም ለኃላፊ ቀርቧል።' : 'የአፈጻጸም ረቂቅ ተቀምጧል።');

        return redirect()
            ->route('admin.performance.show', $report)
            ->with('status', $report->status === ReviewStatus::Submitted ? 'ለኃላፊ ቀርቧል።' : 'ረቂቅ ተቀምጧል።');
    }

    public function show(Request $request, PerformanceReport $report): View
    {
        $report = $this->find($request, $report);
        $report->load(['user', 'organization', 'reviewer']);

        return view('performance.show', [
            'report' => $report,
            'canReview' => $this->canReview($request, $report),
        ]);
    }

    public function edit(Request $request, PerformanceReport $report): View
    {
        $report = $this->editable($request, $report);

        return view('performance.form', [
            'report' => $report,
            'period' => $report->period,
            'budgetYear' => EthiopianDateHelper::currentBudgetYear(),
        ]);
    }

    public function update(Request $request, PerformanceReport $report): RedirectResponse
    {
        $report = $this->editable($request, $report);
        $report->update($this->payload($request, $report));
        PerformanceAlerts::notify($report);
        AuditLogger::record($request->user(), $report, 'updated', $report->status === ReviewStatus::Submitted ? 'አፈጻጸም ለኃላፊ ቀርቧል።' : 'አፈጻጸም ተዘምኗል።');

        return redirect()
            ->route('admin.performance.show', $report)
            ->with('status', $report->status === ReviewStatus::Submitted ? 'ለኃላፊ ቀርቧል።' : 'ተዘምኗል።');
    }

    public function destroy(Request $request, PerformanceReport $report): RedirectResponse
    {
        $report = $this->editable($request, $report);
        $report->delete();

        return redirect()
            ->route('admin.performance.index')
            ->with('status', 'ተሰርዟል።');
    }

    public function approve(Request $request, PerformanceReport $report): RedirectResponse
    {
        return $this->review($request, $report, ReviewStatus::Approved, 'ጸድቋል።');
    }

    public function reject(Request $request, PerformanceReport $report): RedirectResponse
    {
        return $this->review($request, $report, ReviewStatus::Rejected, 'ተቀባይነት አላገኘም።');
    }

    private function review(Request $request, PerformanceReport $report, ReviewStatus $status, string $message): RedirectResponse
    {
        $report = $this->find($request, $report);
        abort_unless($this->canReview($request, $report), 403, 'ይህን አፈጻጸም የማጽደቅ ፈቃድ የለዎትም።');
        abort_unless($report->status === ReviewStatus::Submitted, 403, 'የቀረበ አፈጻጸም ብቻ ይገመገማል።');

        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $report->status = $status;
        $report->reviewed_by = $request->user()->id;
        $report->review_note = $data['review_note'] ?? null;
        $report->save();
        AuditLogger::record($request->user(), $report, $status->value, 'አፈጻጸም፡ '.$message);

        return back()->with('status', $message);
    }

    private function find(Request $request, PerformanceReport $report): PerformanceReport
    {
        $user = $request->user();

        if ($report->user_id === $user->id) {
            return $report;
        }

        abort_unless(
            Roles::allows($user, 'reviewers')
                && in_array((int) $report->organization_id, OrganizationScope::ids(), true),
            404,
        );

        return $report;
    }

    private function editable(Request $request, PerformanceReport $report): PerformanceReport
    {
        $report = $this->find($request, $report);
        abort_unless($report->user_id === $request->user()->id && $report->isOpen(), 403, 'ይህ አፈጻጸም አሁን አይስተካከልም።');

        return $report;
    }

    private function canReview(Request $request, PerformanceReport $report): bool
    {
        return Roles::allows($request->user(), 'reviewers')
            && $report->user_id !== $request->user()->id
            && in_array((int) $report->organization_id, OrganizationScope::ids(), true);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, ?PerformanceReport $report = null): array
    {
        $submitting = $request->input('action') === 'submit';
        $period = PerformancePeriod::tryFrom((string) $request->input('period'));

        $data = $request->validate([
            'period' => ['required', Rule::in(PerformancePeriod::values())],
            'budget_year_ec' => ['required', 'integer', 'min:1900', 'max:2200'],
            'period_number' => ['required', 'integer', 'min:1', 'max:'.($period?->max() ?? 53)],
            'period_label' => ['nullable', 'string', 'max:255'],
            'planned' => ['nullable', 'string', 'max:10000'],
            'accomplished' => [$submitting ? 'required' : 'nullable', 'string', 'max:10000'],
            'challenges' => ['nullable', 'string', 'max:10000'],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'integer' => ':attribute ቁጥር መሆን አለበት።',
            'unique' => 'ለዚህ ወቅት አፈጻጸም ከዚህ በፊት ቀርቧል።',
            'max' => ':attribute ከተፈቀደው በላይ ነው።',
        ], [
            'period' => 'ወቅት',
            'budget_year_ec' => 'የበጀት ዓመት',
            'period_number' => 'ተራ ቁጥር',
            'period_label' => 'የወቅቱ መግለጫ',
            'planned' => 'የታቀደ',
            'accomplished' => 'የተከናወነ',
            'challenges' => 'ፈተናዎች',
        ]);

        $user = $request->user();
        $exists = PerformanceReport::query()
            ->where('user_id', $user->id)
            ->where('period', $data['period'])
            ->where('budget_year_ec', $data['budget_year_ec'])
            ->where('period_number', $data['period_number'])
            ->when($report, fn ($query) => $query->whereKeyNot($report->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'period_number' => 'ለዚህ ወቅት አፈጻጸም ከዚህ በፊት ቀርቧል።',
            ]);
        }

        return [
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'period' => $data['period'],
            'budget_year_ec' => (int) $data['budget_year_ec'],
            'period_number' => (int) $data['period_number'],
            'period_label' => $data['period_label'] ?? null,
            'planned' => $data['planned'] ?? null,
            'accomplished' => $data['accomplished'] ?? null,
            'challenges' => $data['challenges'] ?? null,
            'status' => $submitting ? ReviewStatus::Submitted : ReviewStatus::Draft,
            'submitted_at' => $submitting ? now() : $report?->submitted_at,
            'reviewed_by' => null,
            'review_note' => null,
        ];
    }
}
