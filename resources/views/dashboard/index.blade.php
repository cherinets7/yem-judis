@extends('layouts.app')

@section('title', $roleDash['title'])

@section('content')
    @if (\App\Support\TwoFactor::requiredFor(auth()->user()) && ! \App\Support\TwoFactor::enabled(auth()->user()))
        <div class="alert alert-warning" role="status">
            ለኃላፊ መለያ ሁለት ደረጃ ማረጋገጥ ይመከራል።
            <a href="{{ route('two-factor.edit') }}">አሁን አብሩ</a>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <div class="meta mb-1">{{ $roleDash['role_label'] }}</div>
            <h1 class="h3 mb-1">{{ $roleDash['title'] }}</h1>
            <p class="meta mb-0">{{ $roleDash['subtitle'] }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach ($roleDash['shortcuts'] as $shortcut)
                <a class="btn btn-sm btn-outline-success" href="{{ $shortcut['url'] }}">{{ $shortcut['label'] }}</a>
            @endforeach
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($roleDash['kpis'] as $kpi)
            <div class="col-sm-6 col-xl-3">
                <article class="kpi-card">
                    <div class="kpi-label">{{ $kpi['label'] }}</div>
                    <div class="kpi-value">{{ is_numeric($kpi['value']) ? number_format($kpi['value']) : $kpi['value'] }}</div>
                    <div class="meta">{{ $kpi['meta'] }}</div>
                </article>
            </div>
        @endforeach
    </div>

    @if ($roleDash['show_waiting'] && $performanceReviewer)
        <section class="page-card p-3 p-lg-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                <h2 class="h5 mb-0">ለማጽደቅ የቀረቡ ሪፖርቶች</h2>
                <span class="kpi-value">{{ number_format($waitingTotal) }}</span>
            </div>
            <div class="d-flex flex-wrap gap-3 mb-3">
                @foreach ($queues as $queue)
                    <span class="meta">{{ $queue['label'] }}፡ {{ $queue['count'] }}</span>
                @endforeach
            </div>
            @forelse ($waitingItems as $item)
                <article class="d-flex flex-wrap justify-content-between gap-2 border-bottom py-2">
                    <div>
                        <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                        <div class="meta">{{ $item['label'] }} · {{ $item['meta'] }}</div>
                    </div>
                    <span class="meta">ቀርቧል</span>
                </article>
            @empty
                <div class="empty-state mb-0">ለማጽደቅ የቀረበ ሪፖርት የለም።</div>
            @endforelse
        </section>
    @endif

    @if ($roleDash['show_mine'])
        <section class="page-card p-3 p-lg-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <h2 class="h5 mb-0">{{ $roleDash['mine_label'] }}</h2>
                <a href="{{ route('admin.performance.index', ['mine' => 1]) }}">ሁሉንም ይመልከቱ</a>
            </div>
            @forelse ($performance as $report)
                <article class="d-flex flex-wrap justify-content-between gap-2 border-bottom py-2">
                    <div>
                        <a href="{{ route('admin.performance.show', $report) }}">{{ $report->period->label() }} {{ $report->period_number }}</a>
                        <div class="meta">
                            {{ $report->organization?->amharicName() }}
                            · {{ $report->budget_year_ec }}
                        </div>
                    </div>
                    <span class="meta">{{ $report->status->label() }}</span>
                </article>
            @empty
                <div class="empty-state mb-0">እስካሁን ያቀረቡት አፈጻጸም የለም።</div>
            @endforelse
        </section>
    @endif

    @if ($roleDash['show_matrix'])
        <section class="page-card p-3 p-lg-4">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <h2 class="h5 mb-0">የወረዳ/ከተማ ሪፖርት አቀራረብ</h2>
                <span class="meta">የበጀት ዓመት {{ $budgetYear }}</span>
            </div>
            @if ($matrix->isEmpty())
                <div class="empty-state">በዚህ እይታ ውስጥ ተቋም አልተገኘም።</div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ተቋም</th>
                                <th>አይነት</th>
                                <th>ዓመታዊ</th>
                                <th>ሳምንታዊ</th>
                                <th>ስትራቴጂክ</th>
                                <th>ዐቃቤ ህግ ሳምንታዊ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($matrix as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td>{{ $row['type'] }}</td>
                                    <td>{{ $row['annual'] }}</td>
                                    <td>
                                        {{ $row['weekly'] }}
                                        @if ($row['weekly_count'] > 0)
                                            <span class="meta">· {{ $row['weekly_count'] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $row['strategic'] }}</td>
                                    <td>
                                        {{ $row['prosecutor'] }}
                                        @if ($row['prosecutor_count'] > 0)
                                            <span class="meta">· {{ $row['prosecutor_count'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif
@endsection
