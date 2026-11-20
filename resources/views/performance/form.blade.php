@extends('layouts.app')

@section('title', $report ? 'አፈጻጸም አርም' : 'አፈጻጸም አቅርብ')

@section('content')
    <section class="page-card p-3 p-lg-4">
        <h1 class="h4 mb-1">{{ $report ? 'አፈጻጸም አርም' : 'አፈጻጸም ለኃላፊ አቅርብ' }}</h1>
        <p class="meta mb-3">ሳምንታዊ፣ ወራዊ፣ የሩብ ዓመት ወይም የስድስት ወር አፈጻጸምዎን እራስዎ ይመዝግቡ።</p>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="post" action="{{ $report ? route('admin.performance.update', $report) : route('admin.performance.store') }}">
            @csrf
            @if ($report)
                @method('PUT')
            @endif
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="period">ወቅት</label>
                    <select class="form-select" id="period" name="period" required>
                        @foreach (\App\Enums\PerformancePeriod::cases() as $item)
                            <option value="{{ $item->value }}" data-max="{{ $item->max() }}" @selected(old('period', $report?->period?->value ?? $period->value) === $item->value)>{{ $item->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="budget_year_ec">የበጀት ዓመት</label>
                    <input class="form-control" id="budget_year_ec" name="budget_year_ec" type="number" value="{{ old('budget_year_ec', $report?->budget_year_ec ?? $budgetYear) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="period_number">ተራ ቁጥር</label>
                    <input class="form-control" id="period_number" name="period_number" type="number" min="1" value="{{ old('period_number', $report?->period_number) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="period_label">የወቅቱ መግለጫ</label>
                    <input class="form-control" id="period_label" name="period_label" value="{{ old('period_label', $report?->period_label) }}" placeholder="ምሳሌ፡ ጥቅምት 1–30">
                </div>
                <div class="col-12">
                    <label class="form-label" for="planned">የታቀደ</label>
                    <textarea class="form-control" id="planned" name="planned" rows="3">{{ old('planned', $report?->planned) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="accomplished">የተከናወነ</label>
                    <textarea class="form-control" id="accomplished" name="accomplished" rows="4">{{ old('accomplished', $report?->accomplished) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="challenges">ፈተናዎች</label>
                    <textarea class="form-control" id="challenges" name="challenges" rows="3">{{ old('challenges', $report?->challenges) }}</textarea>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <button class="btn btn-outline-success" name="action" value="draft" type="submit">ረቂቅ አስቀምጥ</button>
                <button class="btn btn-success" name="action" value="submit" type="submit">ለኃላፊ አቅርብ</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.performance.index') }}">ተመለስ</a>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        const period = document.querySelector('#period');
        const number = document.querySelector('#period_number');
        const sync = () => { number.max = period.selectedOptions[0].dataset.max; };
        period.addEventListener('change', sync);
        sync();
    </script>
@endpush
