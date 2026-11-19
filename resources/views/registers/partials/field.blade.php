@php
    use App\Enums\ReviewStatus;

    $span = ($field['span'] ?? null) === 'full' || in_array($field['type'], ['text', 'rows', 'file', 'bool'], true)
        ? 'col-12'
        : 'col-md-6';
    $current = old($field['name'], $record?->{$field['name']} ?? ($field['default'] ?? null));
    if ($current instanceof \BackedEnum) {
        $current = $current->value;
    }
    if ($field['name'] === 'budget_year_ec' && ($current === null || $current === '')) {
        $current = $budgetYear;
    }
@endphp

<div class="{{ $span }}">
    @if ($field['type'] === 'bool')
        <input type="hidden" name="{{ $field['name'] }}" value="0">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" id="field-{{ $field['name'] }}" name="{{ $field['name'] }}" value="1" @checked((string) old($field['name'], $record && $record->{$field['name']} ? '1' : '0') === '1')>
            <label class="form-check-label" for="field-{{ $field['name'] }}">{{ $field['label'] }}</label>
        </div>
    @elseif ($field['name'] === 'status' && $record && in_array($record->status, [ReviewStatus::Approved, ReviewStatus::Rejected], true))
        <div class="form-label">{{ $field['label'] }}</div>
        <div class="form-control-plaintext">{{ $record->status->label() }}</div>
    @elseif ($field['type'] === 'rows')
        @php
            $rows = old($field['name'], $record?->{$field['name']} ?? []);
            if (! is_array($rows)) {
                $rows = [];
            }
        @endphp
        <div class="border rounded-3 p-3" data-row-editor>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="form-label mb-0">{{ $field['label'] }}</div>
                <button class="btn btn-sm btn-outline-success" type="button" data-add-row>ረድፍ ጨምር</button>
            </div>
            <div data-rows>
                @foreach ($rows as $index => $row)
                    <div class="row g-2 align-items-end mb-2" data-row>
                        @foreach ($field['columns'] as $column)
                            <div class="col-md">
                                <label class="form-label">{{ $column['label'] }}</label>
                                <input class="form-control" name="{{ $field['name'] }}[{{ $index }}][{{ $column['name'] }}]" value="{{ $row[$column['name']] ?? '' }}" @if(in_array($column['type'] ?? 'string', ['integer', 'decimal'], true)) type="number" step="any" @else type="text" @endif>
                            </div>
                        @endforeach
                        <div class="col-auto">
                            <button class="btn btn-sm btn-outline-danger" type="button" data-remove-row>አስወግድ</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <template>
                <div class="row g-2 align-items-end mb-2" data-row>
                    @foreach ($field['columns'] as $column)
                        <div class="col-md">
                            <label class="form-label">{{ $column['label'] }}</label>
                            <input class="form-control" name="{{ $field['name'] }}[__INDEX__][{{ $column['name'] }}]" @if(in_array($column['type'] ?? 'string', ['integer', 'decimal'], true)) type="number" step="any" @else type="text" @endif>
                        </div>
                    @endforeach
                    <div class="col-auto">
                        <button class="btn btn-sm btn-outline-danger" type="button" data-remove-row>አስወግድ</button>
                    </div>
                </div>
            </template>
        </div>
    @else
        <label class="form-label" for="field-{{ $field['name'] }}">{{ $field['label'] }}</label>

        @if ($field['type'] === 'text')
            <textarea class="form-control" id="field-{{ $field['name'] }}" name="{{ $field['name'] }}" rows="4" @if($field['required']) required @endif>{{ $current }}</textarea>
        @elseif ($field['type'] === 'enum')
            @php
                $enum = $field['enum'];
                $cases = isset($field['options'])
                    ? array_map(fn ($value) => $enum::from($value), $field['options'])
                    : $enum::cases();
            @endphp
            <select class="form-select" id="field-{{ $field['name'] }}" name="{{ $field['name'] }}" @if($field['required']) required @endif>
                @unless($field['required'])
                    <option value="">ምረጥ</option>
                @endunless
                @foreach ($cases as $case)
                    <option value="{{ $case->value }}" @selected((string) $current === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
        @elseif ($field['type'] === 'work_unit')
            <select class="form-select" id="field-{{ $field['name'] }}" name="{{ $field['name'] }}" data-work-unit @if($field['required']) required @endif>
                @unless($field['required'])
                    <option value="">ምረጥ</option>
                @endunless
                @foreach ($workUnits as $unit)
                    <option value="{{ $unit->id }}" data-organization="{{ $unit->organization_id }}" @selected((string) old($field['name'], $record?->work_unit_id ?? auth()->user()->work_unit_id) === (string) $unit->id)>{{ preg_replace('/\s*\([^)]*\)\s*$/u', '', $unit->name) }}</option>
                @endforeach
            </select>
        @elseif ($field['type'] === 'user')
            <select class="form-select" id="field-{{ $field['name'] }}" name="{{ $field['name'] }}">
                <option value="">ምረጥ</option>
                @foreach ($experts as $expert)
                    <option value="{{ $expert->id }}" @selected((string) old($field['name'], $record?->{$field['name']} ?? auth()->id()) === (string) $expert->id)>{{ $expert->displayName() }}</option>
                @endforeach
            </select>
        @elseif ($field['type'] === 'file')
            <input class="form-control" id="field-{{ $field['name'] }}" name="{{ $field['name'] }}" type="file" accept=".pdf,.txt,.doc,.docx">
            @if ($record && $record->{$field['name']})
                <div class="meta mt-1">
                    ያለ ፋይል፡ {{ basename($record->{$field['name']}) }}
                    @if ($definition['key'] === 'laws')
                        · <a href="{{ route('laws.download', $record) }}">አውርድ</a>
                    @endif
                </div>
            @endif
        @else
            @php
                $inputType = in_array($field['type'], ['integer', 'decimal'], true) ? 'number' : 'text';
            @endphp
            <input
                class="form-control"
                id="field-{{ $field['name'] }}"
                name="{{ $field['name'] }}"
                type="{{ $inputType }}"
                value="{{ $current }}"
                @if($inputType === 'number') step="any" inputmode="decimal" @endif
                @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                @if($field['required']) required @endif
                @if($field['type'] === 'string' && str_ends_with($field['name'], '_ec')) placeholder="ምሳሌ፡ 12/06/2018" @endif
            >
        @endif
    @endif
</div>
