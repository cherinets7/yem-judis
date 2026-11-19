@php
    use App\Enums\ProsecutorReportType;
    use App\Enums\ReviewStatus;
    use App\Support\ExportRecords;
    use App\Support\ProsecutorWeeklyMatrix;
@endphp

@extends('layouts.app')

@section('title', $type ? ProsecutorWeeklyMatrix::title($type) : 'የዐቃቤ ህግ ሳምንታዊ ሪፖርት')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <div class="meta">የዐቃቤ ህግ ሳምንታዊ ማትሪክስ</div>
            <h2 class="h4 mb-0">{{ $type ? ProsecutorWeeklyMatrix::title($type) : 'ሁሉም ሳምንታዊ ሪፖርቶች' }}</h2>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-success" href="{{ route('admin.prosecutor-weekly.bundle.pdf', array_filter(['type' => $type])) }}">PDF ወርድ</a>
            <a class="btn btn-outline-success" href="{{ route('admin.prosecutor-weekly.bundle.excel', array_filter(['type' => $type])) }}">ኤክሴል ወርድ</a>
            @if ($type)
                <a class="btn btn-success" href="{{ route('admin.prosecutor-weekly.create', ['type' => $type]) }}">አዲስ ሪፖርት</a>
            @endif
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a class="btn btn-sm {{ $type === null ? 'btn-success' : 'btn-outline-success' }}" href="{{ route('admin.prosecutor-weekly.index') }}">ሁሉም</a>
        @foreach ($types as $value => $label)
            <a class="btn btn-sm {{ $type === $value ? 'btn-success' : 'btn-outline-success' }}" href="{{ route('admin.prosecutor-weekly.index', ['type' => $value]) }}">{{ ProsecutorReportType::from($value)->label() }}</a>
        @endforeach
    </div>

    @if ($reports->isEmpty())
        <div class="page-card empty-state">አሁን የተመዘገበ ሪፖርት የለም።</div>
    @else
        @foreach ($reports as $report)
            @php
                $payload = ExportRecords::payload($report);
                $summary = $payload['matrix']['summary'];
            @endphp
            <article class="page-card p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <div class="meta">{{ $report->organization?->amharicName() }} · ሳምንት {{ $report->week_number }} · {{ $report->budget_year_ec }}</div>
                        <h3 class="h5 mb-1">{{ ProsecutorWeeklyMatrix::title($report->report_type->value) }}</h3>
                        <div>{{ $report->status->label() }}@if($report->period_covered_ec) · {{ $report->period_covered_ec }}@endif</div>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <div><div class="meta">የማጥራት ምጣኔ</div><div class="fw-bold">{{ $summary['clearance_rate'] === null ? '—' : $summary['clearance_rate'].'%' }}</div></div>
                        <div><div class="meta">የመርታት ምጣኔ</div><div class="fw-bold">{{ $summary['win_rate'] === null ? '—' : $summary['win_rate'].'%' }}</div></div>
                        <div><div class="meta">የማስቀጣት ምጣኔ</div><div class="fw-bold">{{ $summary['conviction_rate'] === null ? '—' : $summary['conviction_rate'].'%' }}</div></div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-success" href="{{ route('admin.prosecutor-weekly.edit', $report) }}">አርም</a>
                    <a class="btn btn-sm btn-outline-success" href="{{ route('admin.prosecutor-weekly.pdf', $report) }}">PDF ወርድ</a>
                    <a class="btn btn-sm btn-outline-success" href="{{ route('admin.prosecutor-weekly.excel', $report) }}">ኤክሴል ወርድ</a>
                    @if ($canReview && $report->status !== ReviewStatus::Approved)
                        <form method="post" action="{{ route('admin.prosecutor-weekly.approve', $report) }}">
                            @csrf
                            <button class="btn btn-sm btn-success" type="submit">አጽድቅ</button>
                        </form>
                    @endif
                    @if ($canReview && $report->status !== ReviewStatus::Rejected)
                        <form method="post" action="{{ route('admin.prosecutor-weekly.reject', $report) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger" type="submit">አትቀበል</button>
                        </form>
                    @endif
                    <form method="post" action="{{ route('admin.prosecutor-weekly.destroy', $report) }}" onsubmit="return confirm('ይህ ሪፖርት ይሰረዝ?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">ሰርዝ</button>
                    </form>
                </div>
            </article>
        @endforeach
        @include('public.partials.pager', ['paginator' => $reports])
    @endif
@endsection
