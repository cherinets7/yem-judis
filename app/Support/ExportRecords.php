<?php

namespace App\Support;

use App\Models\ProsecutorWeeklyReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ExportRecords
{
    /**
     * @return array<string, mixed>
     */
    public static function definition(string $key): array
    {
        try {
            $definition = RegisterCatalog::get($key);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        if ($key === 'staff-profiles') {
            abort_unless(Roles::allows(auth()->user(), Roles::HR), 403, 'ይህን ገጽ የመጠቀም ፈቃድ የለዎትም።');

            return $definition;
        }

        abort_unless(! empty($definition['code']), 404);

        foreach (RegisterCatalog::sections() as $section) {
            if (in_array($key, $section['keys'], true)) {
                abort_unless(Roles::allows(auth()->user(), $section['roles']), 403, 'ይህን ገጽ የመጠቀም ፈቃድ የለዎትም።');

                return $definition;
            }
        }

        abort(404);
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return list<string>
     */
    public static function columns(array $definition): array
    {
        $columns = $definition['columns'];

        if ($definition['scoped'] && ! in_array('organization_id', $columns, true)) {
            array_unshift($columns, 'organization_id');
        }

        return $columns;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function registerQuery(array $definition, Request $request): Builder
    {
        /** @var class-string $model */
        $model = $definition['model'];
        $query = $model::query()->latest();

        if (method_exists($model, 'organization')) {
            $query->with('organization');
        }

        if ($definition['scoped']) {
            $ids = OrganizationScope::ids();
            $query->where(function (Builder $inner) use ($ids, $definition): void {
                $inner->whereIn('organization_id', $ids);

                if ($definition['nullable_org'] && OrganizationScope::includesFamily()) {
                    $inner->orWhereNull('organization_id');
                }
            });
        }

        $term = trim($request->string('q')->toString());

        if ($term !== '' && $definition['search'] !== []) {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where(function (Builder $inner) use ($definition, $like): void {
                foreach ($definition['search'] as $index => $column) {
                    $index === 0
                        ? $inner->where($column, 'like', $like)
                        : $inner->orWhere($column, 'like', $like);
                }
            });
        }

        $level = $request->string('court_level')->toString();
        $hasCourt = collect($definition['fields'])->contains(fn (array $field) => $field['name'] === 'court_level');

        if ($hasCourt && $level !== '') {
            $query->where('court_level', $level);
        }

        return $query;
    }

    public static function report(int $id): ProsecutorWeeklyReport
    {
        return ProsecutorWeeklyReport::query()
            ->with(['items', 'organization', 'user'])
            ->whereIn('organization_id', OrganizationScope::ids())
            ->whereKey($id)
            ->firstOrFail();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function prosecutorPayloads(?string $type = null): array
    {
        return ProsecutorWeeklyReport::query()
            ->with(['items', 'organization'])
            ->whereIn('organization_id', OrganizationScope::ids())
            ->when(
                is_string($type) && array_key_exists($type, ProsecutorWeeklyMatrix::types()),
                fn ($query) => $query->where('report_type', $type),
            )
            ->latest()
            ->get()
            ->map(fn (ProsecutorWeeklyReport $report) => self::payload($report))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function payload(ProsecutorWeeklyReport $report): array
    {
        $saved = [];

        foreach ($report->items as $item) {
            $row = $item->metrics['row'] ?? null;

            if (is_string($row) && $row !== 'total') {
                $saved[$item->section_code][$row] = $item->metrics;
            }
        }

        return [
            'report' => $report,
            'matrix' => ProsecutorWeeklyMatrix::calculate($report->report_type->value, $saved),
            'title' => ProsecutorWeeklyMatrix::title($report->report_type->value),
        ];
    }
}
