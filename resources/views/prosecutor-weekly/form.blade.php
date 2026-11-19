@php
    use App\Enums\ReviewStatus;
    use App\Support\ProsecutorWeeklyMatrix;
@endphp

@extends('layouts.app')

@section('title', $title)

@push('head')
    <style>
        .matrix-wrap { overflow-x: auto; }
        .matrix-table { min-width: 920px; }
        .matrix-table th, .matrix-table td { white-space: nowrap; vertical-align: middle; }
        .matrix-table th:first-child, .matrix-table td:first-child {
            position: sticky;
            left: 0;
            background: var(--card);
            z-index: 1;
        }
        .matrix-table tr.is-total td { background: var(--gold-soft); font-weight: 700; }
        .matrix-input { width: 6.2rem; }
        .rate-strip { background: #e7f0ea; border-radius: .8rem; padding: .75rem 1rem; }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <div class="meta">ሳምንታዊ ማትሪክስ</div>
            <h2 class="h4 mb-0">{{ $title }}</h2>
        </div>
        @if ($report)
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-success" href="{{ route('admin.prosecutor-weekly.pdf', $report) }}">PDF ወርድ</a>
                <a class="btn btn-outline-success" href="{{ route('admin.prosecutor-weekly.excel', $report) }}">ኤክሴል ወርድ</a>
            </div>
        @endif
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-bold mb-1">ማስተካከል ያለባቸው ቦታዎች አሉ።</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="weekly-matrix" method="post" action="{{ $report ? route('admin.prosecutor-weekly.update', $report) : route('admin.prosecutor-weekly.store') }}">
        @csrf
        @if ($report)
            @method('PUT')
        @endif
        <input type="hidden" name="report_type" value="{{ $type }}">

        <div class="page-card p-3 p-lg-4 mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="organization_id">ተቋም</label>
                    @if ($organizations->count() <= 1)
                        <input type="hidden" name="organization_id" value="{{ old('organization_id', $report?->organization_id ?? $organizations->first()?->id) }}">
                        <div class="form-control-plaintext">{{ $organizations->first()?->amharicName() ?? '—' }}</div>
                    @else
                        <select class="form-select" id="organization_id" name="organization_id" required>
                            <option value="">ምረጥ</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}" @selected((string) old('organization_id', $report?->organization_id ?? '') === (string) $organization->id)>{{ $organization->amharicName() }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="budget_year_ec">የበጀት ዓመት</label>
                    <input class="form-control" id="budget_year_ec" name="budget_year_ec" type="number" min="1900" max="2200" required value="{{ old('budget_year_ec', $report?->budget_year_ec ?? $budgetYear) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="week_number">ሳምንት</label>
                    <input class="form-control" id="week_number" name="week_number" type="number" min="1" max="53" required value="{{ old('week_number', $report?->week_number ?? 1) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="period_covered_ec">የተሸፈነው ጊዜ</label>
                    <input class="form-control" id="period_covered_ec" name="period_covered_ec" maxlength="255" value="{{ old('period_covered_ec', $report?->period_covered_ec) }}" placeholder="ለምሳሌ ኅዳር 4–10">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="status">ሁኔታ</label>
                    @php $locked = $report && in_array($report->status, [ReviewStatus::Approved, ReviewStatus::Rejected], true); @endphp
                    @if ($locked)
                        <div class="form-control-plaintext">{{ $report->status->label() }}</div>
                    @else
                        <select class="form-select" id="status" name="status" required>
                            @foreach ([ReviewStatus::Draft, ReviewStatus::Submitted] as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $report?->status->value ?? ReviewStatus::Draft->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </div>

        <div class="rate-strip d-flex flex-wrap gap-4 mb-3" aria-live="polite">
            <div><div class="meta">የማጥራት ምጣኔ</div><div class="fw-bold" data-summary="clearance_rate">{{ $matrix['summary']['clearance_rate'] === null ? '—' : $matrix['summary']['clearance_rate'].'%' }}</div></div>
            <div><div class="meta">የመርታት ምጣኔ</div><div class="fw-bold" data-summary="win_rate">{{ $matrix['summary']['win_rate'] === null ? '—' : $matrix['summary']['win_rate'].'%' }}</div></div>
            <div><div class="meta">የማስቀጣት ምጣኔ</div><div class="fw-bold" data-summary="conviction_rate">{{ $matrix['summary']['conviction_rate'] === null ? '—' : $matrix['summary']['conviction_rate'].'%' }}</div></div>
        </div>

        <p class="meta">ቁጥሩን ሲያስገቡ ድምር፣ ያልተወሰነ እና ሦስቱ ምጣኔዎች ወዲያውኑ ይሰላሉ። የማጥራት ምጣኔ የተጠናቀቀውን ከድምሩ ጋር፣ የመርታት ምጣኔ የተገኘውን ውጤት ከተወሰነው ጋር፣ የማስቀጣት ምጣኔ ቅጣትን ከድል ጋር ያመዝናል። መለያው ዜሮ ሲሆን ምጣኔው — ይታያል።</p>

        @include('prosecutor-weekly.partials.matrix')

        <div class="d-flex flex-wrap gap-2 mt-3">
            <button class="btn btn-success" type="submit">{{ $report ? 'አዘምን' : 'መዝግብ' }}</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.prosecutor-weekly.index', ['type' => $type]) }}">ተመለስ</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const definition = @json($clientDefinition);
            const form = document.getElementById('weekly-matrix');
            if (!form) return;

            function evalExpr(expression, values) {
                if (typeof expression === 'string') {
                    const value = values[expression];
                    return value == null || value === '' ? 0 : Number(value);
                }
                if (typeof expression === 'number') return expression;
                if (!expression || typeof expression !== 'object') return 0;
                if (expression.add) {
                    return Math.trunc(expression.add.reduce(function (sum, part) {
                        return sum + Number(evalExpr(part, values) || 0);
                    }, 0));
                }
                if (expression.sub) {
                    return Math.trunc(Number(evalExpr(expression.sub[0], values) || 0) - Number(evalExpr(expression.sub[1], values) || 0));
                }
                if (expression.percent) {
                    const denominator = Number(evalExpr(expression.percent[1], values) || 0);
                    if (denominator <= 0) return null;
                    const numerator = Number(evalExpr(expression.percent[0], values) || 0);
                    return Math.round((numerator / denominator) * 1000) / 10;
                }
                return 0;
            }

            function format(key, value) {
                if (value == null || value === '') return '—';
                if (String(key).endsWith('_rate')) return Number(value).toFixed(1) + '%';
                return String(value);
            }

            function read(section, row, key) {
                const input = form.querySelector('[data-section="' + section + '"][data-row="' + row + '"][data-key="' + key + '"]');
                const number = input ? Number(input.value) : 0;
                return Number.isFinite(number) && number > 0 ? Math.trunc(number) : 0;
            }

            function paint(section, row, inputs) {
                const values = {};
                section.inputs.forEach(function (key) { values[key] = inputs[key] || 0; });
                Object.entries(section.steps).forEach(function (entry) {
                    values[entry[0]] = evalExpr(entry[1], values);
                });
                Object.entries(values).forEach(function (entry) {
                    const cell = form.querySelector('[data-cell="' + section.code + '.' + row + '.' + entry[0] + '"]');
                    if (cell) cell.textContent = format(entry[0], entry[1]);
                });
                return values;
            }

            function recalculate() {
                const weights = {
                    clearance_rate: [0, 0],
                    win_rate: [0, 0],
                    conviction_rate: [0, 0]
                };

                definition.sections.forEach(function (section) {
                    const dataRows = section.rows.filter(function (code) { return code !== 'total'; });
                    const sums = {};
                    section.inputs.forEach(function (key) {
                        sums[key] = dataRows.reduce(function (sum, code) {
                            return sum + read(section.code, code, key);
                        }, 0);
                    });

                    dataRows.forEach(function (code) {
                        const inputs = {};
                        section.inputs.forEach(function (key) { inputs[key] = read(section.code, code, key); });
                        const metrics = paint(section, code, inputs);
                        Object.entries(section.rates).forEach(function (entry) {
                            weights[entry[0]][0] += Number(evalExpr(entry[1].num, metrics) || 0);
                            weights[entry[0]][1] += Number(evalExpr(entry[1].den, metrics) || 0);
                        });
                    });

                    paint(section, 'total', sums);
                });

                Object.entries(weights).forEach(function (entry) {
                    const cell = form.querySelector('[data-summary="' + entry[0] + '"]');
                    if (!cell) return;
                    cell.textContent = entry[1][1] > 0
                        ? (Math.round((entry[1][0] / entry[1][1]) * 1000) / 10).toFixed(1) + '%'
                        : '—';
                });
            }

            form.addEventListener('input', recalculate);
            recalculate();
        })();
    </script>
@endpush
