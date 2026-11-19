<?php

namespace App\Http\Controllers;

use App\Enums\ProsecutorReportType;
use App\Enums\ReviewStatus;
use App\Helpers\EthiopianDateHelper;
use App\Models\Organization;
use App\Models\ProsecutorWeeklyReport;
use App\Support\OrganizationScope;
use App\Support\ProsecutorWeeklyMatrix;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProsecutorWeeklyReportController extends Controller
{
    public function index(Request $request): View
    {
        $type = $this->type($request->string('type')->toString());
        $reports = ProsecutorWeeklyReport::query()
            ->with(['organization', 'items'])
            ->whereIn('organization_id', OrganizationScope::ids())
            ->when($type !== null, fn ($query) => $query->where('report_type', $type))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('prosecutor-weekly.index', [
            'reports' => $reports,
            'type' => $type,
            'types' => ProsecutorWeeklyMatrix::types(),
            'canReview' => Roles::allows($request->user(), Roles::REVIEWERS),
        ]);
    }

    public function create(Request $request): View
    {
        $type = $this->type($request->string('type')->toString()) ?? ProsecutorReportType::Civil->value;

        return view('prosecutor-weekly.form', $this->formData($request, $type));
    }

    public function store(Request $request): RedirectResponse
    {
        $header = $this->validateHeader($request);
        $type = $header['report_type'];
        $matrix = ProsecutorWeeklyMatrix::calculate($type, $request->input('rows', []));

        DB::transaction(function () use ($request, $header, $matrix): void {
            $report = ProsecutorWeeklyReport::query()->create([
                ...$header,
                'user_id' => $request->user()->id,
            ]);
            $this->storeItems($report, $matrix);
        });

        return redirect()
            ->route('admin.prosecutor-weekly.index', ['type' => $type])
            ->with('status', 'ተመዝግቧል።');
    }

    public function edit(Request $request): View
    {
        $report = $this->find($request);

        return view('prosecutor-weekly.form', $this->formData($request, $report->report_type->value, $report));
    }

    public function update(Request $request): RedirectResponse
    {
        $report = $this->find($request);
        $header = $this->validateHeader($request, $report);
        $matrix = ProsecutorWeeklyMatrix::calculate($report->report_type->value, $request->input('rows', []));

        DB::transaction(function () use ($report, $header, $matrix): void {
            $report->update($header);
            $report->items()->delete();
            $this->storeItems($report, $matrix);
        });

        return redirect()
            ->route('admin.prosecutor-weekly.edit', $report)
            ->with('status', 'ተዘምኗል።');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $report = $this->find($request);
        $type = $report->report_type->value;
        $report->delete();

        return redirect()
            ->route('admin.prosecutor-weekly.index', ['type' => $type])
            ->with('status', 'ተሰርዟል።');
    }

    public function approve(Request $request): RedirectResponse
    {
        return $this->review($request, ReviewStatus::Approved, 'ጸድቋል።');
    }

    public function reject(Request $request): RedirectResponse
    {
        return $this->review($request, ReviewStatus::Rejected, 'ተቀባይነት አላገኘም።');
    }

    /**
     * @param  array{sections: list<array<string, mixed>>}  $matrix
     */
    private function storeItems(ProsecutorWeeklyReport $report, array $matrix): void
    {
        foreach ($matrix['sections'] as $section) {
            foreach ($section['rows'] as $row) {
                $report->items()->create([
                    'section_code' => $section['code'],
                    'row_label' => $row['label'],
                    'metrics' => ['row' => $row['code']] + $row['metrics'],
                ]);
            }
        }
    }

    private function review(Request $request, ReviewStatus $status, string $message): RedirectResponse
    {
        abort_unless(Roles::allows($request->user(), Roles::REVIEWERS), 403, 'ይህን ገጽ የመጠቀም ፈቃድ የለዎትም።');

        $report = $this->find($request);
        $report->status = $status;
        $report->reviewed_by = $request->user()->id;
        $report->save();

        return back()->with('status', $message);
    }

    private function find(Request $request): ProsecutorWeeklyReport
    {
        return ProsecutorWeeklyReport::query()
            ->with(['items', 'organization', 'user'])
            ->whereIn('organization_id', OrganizationScope::ids())
            ->whereKey($request->route('report'))
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request, string $type, ?ProsecutorWeeklyReport $report = null): array
    {
        $ids = OrganizationScope::ids();
        $posted = $request->old('rows', $this->savedRows($report));

        return [
            'report' => $report,
            'type' => $type,
            'title' => ProsecutorWeeklyMatrix::title($type),
            'matrix' => ProsecutorWeeklyMatrix::calculate($type, is_array($posted) ? $posted : []),
            'clientDefinition' => ProsecutorWeeklyMatrix::clientDefinition($type),
            'organizations' => Organization::query()->whereIn('id', $ids)->orderBy('name')->get(),
            'budgetYear' => EthiopianDateHelper::currentBudgetYear(),
            'canReview' => Roles::allows($request->user(), Roles::REVIEWERS),
        ];
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function savedRows(?ProsecutorWeeklyReport $report): array
    {
        if ($report === null) {
            return [];
        }

        $rows = [];

        foreach ($report->items as $item) {
            $code = $item->metrics['row'] ?? null;

            if (! is_string($code) || $code === 'total') {
                continue;
            }

            $rows[$item->section_code][$code] = $item->metrics;
        }

        return $rows;
    }

    /**
     * @return array{organization_id: int, report_type: string, budget_year_ec: int, week_number: int, period_covered_ec: ?string, status: string}
     */
    private function validateHeader(Request $request, ?ProsecutorWeeklyReport $report = null): array
    {
        if (! $request->filled('organization_id')) {
            $request->merge(['organization_id' => $request->user()->organization_id]);
        }

        if ($report !== null) {
            $request->merge(['report_type' => $report->report_type->value]);
        }

        $locked = $report !== null && in_array($report->status, [ReviewStatus::Approved, ReviewStatus::Rejected], true);
        $this->normalizeRows($request);

        $data = $request->validate([
            'organization_id' => ['required', 'integer', Rule::in(OrganizationScope::ids())],
            'report_type' => ['required', Rule::in(array_keys(ProsecutorWeeklyMatrix::types()))],
            'budget_year_ec' => ['required', 'integer', 'min:1900', 'max:2200'],
            'week_number' => [
                'required',
                'integer',
                'min:1',
                'max:53',
                Rule::unique('prosecutor_weekly_reports', 'week_number')->where(function ($query) use ($request): void {
                    $query->where('organization_id', $request->input('organization_id'))
                        ->where('user_id', $request->user()->id)
                        ->where('report_type', $request->input('report_type'))
                        ->where('budget_year_ec', $request->input('budget_year_ec'));
                })->ignore($report?->id),
            ],
            'period_covered_ec' => ['nullable', 'string', 'max:255'],
            'status' => $locked
                ? ['nullable', 'string']
                : ['required', Rule::in([ReviewStatus::Draft->value, ReviewStatus::Submitted->value])],
            'rows' => ['nullable', 'array'],
            'rows.*' => ['array'],
            'rows.*.*' => ['array'],
            'rows.*.*.*' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ], [
            'required' => ':attribute ያስፈልጋል።',
            'integer' => ':attribute ቁጥር መሆን አለበት።',
            'unique' => 'ይህ ሳምንት ከዚህ በፊት ተመዝግቧል።',
            'in' => ':attribute ትክክል አይደለም።',
            'min' => ':attribute ከተፈቀደው በታች ነው።',
            'max' => ':attribute ከተፈቀደው በላይ ነው።',
        ], [
            'organization_id' => 'ተቋም',
            'report_type' => 'የሪፖርት አይነት',
            'budget_year_ec' => 'የበጀት ዓመት',
            'week_number' => 'ሳምንት',
            'period_covered_ec' => 'የተሸፈነው ጊዜ',
            'status' => 'ሁኔታ',
        ]);

        if ($locked) {
            unset($data['status']);
        }

        unset($data['rows']);

        return $data;
    }

    private function normalizeRows(Request $request): void
    {
        $rows = $request->input('rows');

        if (! is_array($rows)) {
            return;
        }

        array_walk_recursive($rows, function (&$value): void {
            if ($value === '' || $value === null) {
                $value = 0;
            }
        });

        $request->merge(['rows' => $rows]);
    }

    private function type(string $type): ?string
    {
        return array_key_exists($type, ProsecutorWeeklyMatrix::types()) ? $type : null;
    }
}
