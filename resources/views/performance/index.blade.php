@extends('layouts.app')

@section('title', 'አፈጻጸም')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0">አፈጻጸም</h1>
            @if ($reviewer && $waiting > 0)
                <div class="meta">{{ $waiting }} ለግምገማ ቀርቧል</div>
            @endif
        </div>
        <a class="btn btn-success" href="{{ route('admin.performance.create', ['period' => $period?->value]) }}">አዲስ አፈጻጸም</a>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a class="btn btn-sm {{ $period ? 'btn-outline-success' : 'btn-success' }}" href="{{ route('admin.performance.index', array_filter(['mine' => $mine ? 1 : null, 'status' => $status?->value])) }}">ሁሉም ወቅት</a>
        @foreach (\App\Enums\PerformancePeriod::cases() as $item)
            <a class="btn btn-sm {{ $period === $item ? 'btn-success' : 'btn-outline-success' }}" href="{{ route('admin.performance.index', array_filter(['period' => $item->value, 'mine' => $mine ? 1 : null, 'status' => $status?->value])) }}">{{ $item->label() }}</a>
        @endforeach
    </div>

    @if ($reviewer)
        <div class="d-flex gap-2 mb-3">
            <a class="btn btn-sm {{ $mine ? 'btn-outline-success' : 'btn-success' }}" href="{{ route('admin.performance.index', array_filter(['period' => $period?->value])) }}">የቢሮ አፈጻጸም</a>
            <a class="btn btn-sm {{ $mine ? 'btn-success' : 'btn-outline-success' }}" href="{{ route('admin.performance.index', array_filter(['period' => $period?->value, 'mine' => 1])) }}">የኔ አፈጻጸም</a>
        </div>
    @endif

    @if ($reports->isEmpty())
        <div class="page-card empty-state">አፈጻጸም አልተገኘም።</div>
    @else
        <div class="page-card p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ባለሙያ</th>
                            <th>ወቅት</th>
                            <th>ዓመት</th>
                            <th>ሁኔታ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td>{{ $report->user?->displayName() }}</td>
                                <td>{{ $report->period->label() }} {{ $report->period_number }}</td>
                                <td>{{ $report->budget_year_ec }}</td>
                                <td>{{ $report->status->label() }}</td>
                                <td><a class="btn btn-sm btn-outline-success" href="{{ route('admin.performance.show', $report) }}">ይመልከቱ</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $reports->links() }}</div>
        </div>
    @endif
@endsection
