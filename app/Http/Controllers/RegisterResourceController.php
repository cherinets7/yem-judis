<?php

namespace App\Http\Controllers;

use App\Enums\CourtLevel;
use App\Enums\ReviewStatus;
use App\Helpers\EthiopianDateHelper;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkUnit;
use App\Support\OrganizationScope;
use App\Support\PublicCaseProjector;
use App\Support\RegisterCatalog;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

abstract class RegisterResourceController extends Controller
{
    protected string $register;

    public function index(Request $request): View
    {
        $definition = $this->definition();
        $query = $this->scopedQuery();
        $this->applySearch($query, $request, $definition);
        $this->applyCourtFilter($query, $request, $definition);

        $records = $query->latest()->paginate(12)->withQueryString();

        return view('registers.index', $this->page([
            'records' => $records,
            'columns' => $this->columns($definition),
            'q' => trim($request->string('q')->toString()),
        ]));
    }

    public function create(): View
    {
        return view('registers.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $definition = $this->definition();
        $data = $this->validated($request, $definition);
        $record = $definition['model']::query()->create($data);
        $this->project($definition, $record);

        return redirect()
            ->route($this->routeName('index'))
            ->with('status', 'ተመዝግቧል።');
    }

    public function edit(Request $request): View
    {
        return view('registers.edit', $this->formData($this->find($request)));
    }

    public function update(Request $request): RedirectResponse
    {
        $definition = $this->definition();
        $record = $this->find($request);
        $record->update($this->validated($request, $definition, $record));
        $this->project($definition, $record);

        return redirect()
            ->route($this->routeName('edit'), $record)
            ->with('status', 'ተዘምኗል።');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $record = $this->find($request);
        $this->project($this->definition(), $record, true);
        $record->delete();

        return redirect()
            ->route($this->routeName('index'))
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

    public function publish(Request $request): RedirectResponse
    {
        $definition = $this->definition();
        abort_unless($definition['publish'], 404);

        $record = $this->find($request);
        $published = $request->boolean('is_published');

        if ($definition['project_public'] ?? false) {
            $tracking = PublicCaseProjector::sync($record);
            abort_if($tracking === null, 404);
            $tracking->is_published = $published;
            $tracking->updated_by = $request->user()->id;
            $tracking->save();

            return back()->with('status', $published ? 'ለህዝብ ታትሟል።' : 'ከህዝብ ተደብቋል።');
        }

        $record->is_published = $published;

        if (in_array('updated_by', $record->getFillable(), true)) {
            $record->updated_by = $request->user()->id;
        }

        $record->save();

        return back()->with('status', $published ? 'ለህዝብ ታትሟል።' : 'ከህዝብ ተደብቋል።');
    }

    /**
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        return RegisterCatalog::get($this->register);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected function project(array $definition, Model $record, bool $forget = false): void
    {
        if (! ($definition['project_public'] ?? false)) {
            return;
        }

        if ($forget) {
            PublicCaseProjector::forget($record);

            return;
        }

        PublicCaseProjector::sync($record->fresh() ?? $record);
    }

    protected function find(Request $request): Model
    {
        $id = $request->route('record');

        return $this->scopedQuery()->whereKey($id)->firstOrFail();
    }

    protected function scopedQuery(): Builder
    {
        $definition = $this->definition();
        /** @var class-string<Model> $model */
        $model = $definition['model'];
        $query = $model::query();
        $instance = new $model;

        if (method_exists($instance, 'organization')) {
            $query->with('organization');
        }

        if ($definition['project_public'] ?? false) {
            $query->with('publicTracking');
        }

        if (method_exists($instance, 'workUnit')) {
            $query->with('workUnit');
        }

        foreach (['expert', 'filledByExpert', 'approvedByExpert', 'servedByExpert'] as $relation) {
            if (method_exists($instance, $relation)) {
                $query->with($relation);
            }
        }

        if (! $definition['scoped']) {
            return $query;
        }

        $ids = OrganizationScope::ids();

        return $query->where(function (Builder $inner) use ($ids, $definition): void {
            $inner->whereIn('organization_id', $ids);

            if ($definition['nullable_org'] && OrganizationScope::includesFamily()) {
                $inner->orWhereNull('organization_id');
            }
        });
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected function applySearch(Builder $query, Request $request, array $definition): void
    {
        $term = trim($request->string('q')->toString());

        if ($term === '' || $definition['search'] === []) {
            return;
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        $query->where(function (Builder $inner) use ($definition, $like): void {
            foreach ($definition['search'] as $index => $column) {
                if ($index === 0) {
                    $inner->where($column, 'like', $like);
                } else {
                    $inner->orWhere($column, 'like', $like);
                }
            }
        });
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected function applyCourtFilter(Builder $query, Request $request, array $definition): void
    {
        $level = $request->string('court_level')->toString();
        $hasCourt = collect($definition['fields'])->contains(fn (array $field) => $field['name'] === 'court_level');

        if ($hasCourt && in_array($level, CourtLevel::values(), true)) {
            $query->where('court_level', $level);
        }
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return list<string>
     */
    protected function columns(array $definition): array
    {
        $columns = $definition['columns'];

        if ($definition['scoped'] && ! in_array('organization_id', $columns, true)) {
            array_unshift($columns, 'organization_id');
        }

        return $columns;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    protected function validated(Request $request, array $definition, ?Model $record = null): array
    {
        $this->prepare($request, $definition);

        $data = $request->validate(
            $this->rules($request, $definition, $record),
            $this->messages(),
            $this->attributes($definition),
        );

        return $this->finalize($request, $definition, $data, $record);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected function prepare(Request $request, array $definition): void
    {
        $merge = [];

        foreach ($definition['fields'] as $field) {
            $name = $field['name'];

            if ($field['type'] === 'bool') {
                $merge[$name] = $request->boolean($name) ? 1 : 0;

                continue;
            }

            if ($field['type'] === 'rows') {
                $rows = $request->input($name, []);

                if (! is_array($rows)) {
                    continue;
                }

                foreach ($rows as $index => $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    foreach ($field['columns'] as $column) {
                        $cell = $row[$column['name']] ?? null;

                        if ($cell === '' && in_array($column['type'] ?? 'string', ['integer', 'decimal'], true)) {
                            $rows[$index][$column['name']] = null;
                        }
                    }
                }

                $merge[$name] = $rows;

                continue;
            }

            if (! $request->exists($name)) {
                continue;
            }

            if ($request->input($name) === '' && in_array($field['type'], ['integer', 'decimal', 'enum', 'work_unit', 'user'], true)) {
                $merge[$name] = array_key_exists('empty', $field) ? $field['empty'] : null;
            }
        }

        if ($definition['scoped'] && ! $request->filled('organization_id')) {
            $merge['organization_id'] = $request->user()->organization_id;
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    protected function rules(Request $request, array $definition, ?Model $record): array
    {
        $rules = [];

        if ($definition['scoped']) {
            $rules['organization_id'] = ['required', 'integer', Rule::in(OrganizationScope::ids())];
        }

        foreach ($definition['fields'] as $field) {
            if (
                $field['name'] === 'status'
                && $record !== null
                && in_array($record->status, [ReviewStatus::Approved, ReviewStatus::Rejected], true)
            ) {
                continue;
            }

            $rules[$field['name']] = $this->fieldRules($request, $definition, $field, $record);

            if ($field['type'] !== 'rows') {
                continue;
            }

            foreach ($field['columns'] as $column) {
                $columnRules = ['nullable'];

                if (($column['type'] ?? 'string') === 'integer') {
                    $columnRules[] = 'integer';
                    $columnRules[] = 'min:1900';
                    $columnRules[] = 'max:2200';
                } elseif (($column['type'] ?? 'string') === 'decimal') {
                    $columnRules[] = 'numeric';
                    $columnRules[] = 'min:0';
                } else {
                    $columnRules[] = 'string';
                    $columnRules[] = 'max:255';
                }

                $rules[$field['name'].'.*.'.$column['name']] = $columnRules;
            }
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $field
     * @return list<mixed>
     */
    protected function fieldRules(Request $request, array $definition, array $field, ?Model $record): array
    {
        $rules = [$field['required'] ? 'required' : 'nullable'];

        switch ($field['type']) {
            case 'string':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case 'text':
                $rules[] = 'string';
                $rules[] = 'max:10000';
                break;
            case 'integer':
                $rules[] = 'integer';
                $rules[] = 'min:'.($field['min'] ?? 0);
                $rules[] = 'max:'.($field['max'] ?? 4294967295);
                break;
            case 'decimal':
                $rules[] = 'numeric';
                $rules[] = 'min:'.($field['min'] ?? 0);
                $rules[] = 'max:'.($field['max'] ?? 999999999999.99);
                break;
            case 'bool':
                $rules = ['required', 'boolean'];
                break;
            case 'enum':
                $values = $field['options'] ?? array_map(
                    fn ($case) => $case->value,
                    $field['enum']::cases(),
                );
                $rules[] = Rule::in($values);
                break;
            case 'file':
                $rules[] = 'file';
                $rules[] = 'mimes:pdf,txt,doc,docx';
                $rules[] = 'max:10240';
                break;
            case 'work_unit':
                $rules[] = 'integer';
                $rules[] = Rule::exists('work_units', 'id')->where(
                    fn ($query) => $query->where('organization_id', $request->input('organization_id')),
                );
                break;
            case 'user':
                $rules[] = 'integer';
                $rules[] = Rule::exists('users', 'id')->where(
                    fn ($query) => $query->whereIn('organization_id', OrganizationScope::ids()),
                );
                break;
            case 'rows':
                $rules[] = 'array';
                break;
        }

        $unique = $definition['unique'] ?? null;

        if (is_array($unique) && $unique !== [] && $field['name'] === $unique[array_key_last($unique)]) {
            /** @var Model $model */
            $model = new $definition['model'];
            $rule = Rule::unique($model->getTable(), $field['name'])->where(function ($query) use ($request, $unique): void {
                foreach (array_slice($unique, 0, -1) as $column) {
                    $query->where($column, $request->input($column));
                }
            });

            if ($record !== null) {
                $rule->ignore($record->getKey());
            }

            $rules[] = $rule;
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function finalize(Request $request, array $definition, array $data, ?Model $record): array
    {
        /** @var Model $model */
        $model = new $definition['model'];
        $fillable = $model->getFillable();
        $payload = [];

        foreach ($definition['fields'] as $field) {
            $name = $field['name'];

            if ($field['type'] === 'file') {
                if ($request->hasFile($name)) {
                    $payload[$name] = $request->file($name)->store($field['directory'] ?? 'registers', 'local');
                    $this->deleteStoredFile($record?->getAttribute($name));
                }

                continue;
            }

            if ($field['type'] === 'rows') {
                $payload[$name] = $this->cleanRows($data[$name] ?? [], $field['columns']);

                continue;
            }

            if (array_key_exists($name, $data)) {
                $payload[$name] = $data[$name];
            }
        }

        if ($definition['scoped'] && in_array('organization_id', $fillable, true)) {
            $payload['organization_id'] = (int) $data['organization_id'];
        }

        if (
            $record !== null
            && $definition['review']
            && in_array($record->status, [ReviewStatus::Approved, ReviewStatus::Rejected], true)
        ) {
            unset($payload['status']);
        }

        if (isset($payload['status']) && in_array($payload['status'], [ReviewStatus::Approved->value, ReviewStatus::Rejected->value], true)) {
            unset($payload['status']);
        }

        $userId = $request->user()->id;

        if ($record === null) {
            foreach (['user_id', 'created_by', 'uploaded_by'] as $column) {
                if (in_array($column, $fillable, true)) {
                    $payload[$column] = $userId;
                }
            }
        }

        if (in_array('updated_by', $fillable, true)) {
            $payload['updated_by'] = $userId;
        }

        foreach (['filled_by_expert', 'approved_by_expert', 'served_by_expert'] as $column) {
            if (in_array($column, $fillable, true) && empty($payload[$column])) {
                $payload[$column] = $userId;
            }
        }

        return array_intersect_key($payload, array_flip($fillable));
    }

    /**
     * @param  list<array<string, mixed>>  $columns
     * @return list<array<string, mixed>>|null
     */
    protected function cleanRows(mixed $rows, array $columns): ?array
    {
        if (! is_array($rows)) {
            return null;
        }

        $clean = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $item = [];
            $filled = false;

            foreach ($columns as $column) {
                $value = $row[$column['name']] ?? null;

                if ($value === '' || $value === null) {
                    $item[$column['name']] = null;

                    continue;
                }

                $filled = true;

                if (($column['type'] ?? 'string') === 'integer' && is_numeric($value)) {
                    $value = (int) $value;
                }

                $item[$column['name']] = $value;
            }

            if ($filled) {
                $clean[] = $item;
            }
        }

        return $clean === [] ? null : $clean;
    }

    protected function review(Request $request, ReviewStatus $status, string $message): RedirectResponse
    {
        $definition = $this->definition();
        abort_unless($definition['review'], 404);
        abort_unless(Roles::allows($request->user(), Roles::REVIEWERS), 403, 'ይህን ገጽ የመጠቀም ፈቃድ የለዎትም።');

        if ($definition['note']) {
            $request->validate([
                'review_note' => ['nullable', 'string', 'max:2000'],
            ], $this->messages(), ['review_note' => 'የግምገማ ማስታወሻ']);
        }

        $record = $this->find($request);
        $record->status = $status;

        if ($definition['reviewer']) {
            $record->reviewed_by = $request->user()->id;
        }

        if ($definition['note'] && $request->filled('review_note')) {
            $record->review_note = $request->string('review_note')->toString();
        }

        $record->save();

        return back()->with('status', $message);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function formData(?Model $record = null): array
    {
        $ids = OrganizationScope::ids();
        $expertIds = User::query()
            ->whereIn('organization_id', $ids)
            ->where('is_active', true)
            ->pluck('id');

        if ($record !== null) {
            foreach (['expert_id', 'filled_by_expert', 'approved_by_expert', 'served_by_expert'] as $column) {
                $selected = $record->getAttribute($column);

                if ($selected) {
                    $expertIds->push($selected);
                }
            }
        }

        return $this->page([
            'record' => $record,
            'organizations' => Organization::query()
                ->whereIn('id', $ids)
                ->where(function ($query) use ($record): void {
                    $query->where('is_active', true);

                    if ($record?->getAttribute('organization_id')) {
                        $query->orWhere('id', $record->getAttribute('organization_id'));
                    }
                })
                ->orderBy('name')
                ->get(),
            'workUnits' => WorkUnit::query()->whereIn('organization_id', $ids)->orderBy('name')->get(),
            'experts' => User::query()->whereIn('id', $expertIds->unique()->all())->orderBy('name')->get(),
            'budgetYear' => EthiopianDateHelper::currentBudgetYear(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function page(array $extra = []): array
    {
        return array_merge([
            'definition' => $this->definition(),
            'canReview' => Roles::allows(auth()->user(), Roles::REVIEWERS),
        ], $extra);
    }

    protected function routeName(string $action): string
    {
        return 'admin.'.$this->definition()['uri'].'.'.$action;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, string>
     */
    protected function attributes(array $definition): array
    {
        $attributes = ['organization_id' => 'ተቋም'];

        foreach ($definition['fields'] as $field) {
            $attributes[$field['name']] = $field['label'];

            if ($field['type'] !== 'rows') {
                continue;
            }

            foreach ($field['columns'] as $column) {
                $attributes[$field['name'].'.*.'.$column['name']] = $column['label'];
            }
        }

        return $attributes;
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => ':attribute ያስፈልጋል።',
            'integer' => ':attribute ቁጥር መሆን አለበት።',
            'numeric' => ':attribute ቁጥር መሆን አለበት።',
            'in' => ':attribute ትክክል አይደለም።',
            'unique' => 'ይህ መዝገብ ከዚህ በፊት ተመዝግቧል።',
            'max' => ':attribute ከተፈቀደው በላይ ነው።',
            'min' => ':attribute ከተፈቀደው በታች ነው።',
            'exists' => ':attribute አልተገኘም።',
            'file' => ':attribute ፋይል መሆን አለበት።',
            'mimes' => ':attribute የተፈቀደ የፋይል አይነት አይደለም።',
            'boolean' => ':attribute ትክክል አይደለም።',
            'array' => ':attribute ትክክል አይደለም።',
        ];
    }

    protected function deleteStoredFile(mixed $path): void
    {
        if (! is_string($path) || $path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            return;
        }

        Storage::disk('local')->delete($path);
    }
}
