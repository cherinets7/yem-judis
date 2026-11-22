@extends('layouts.app')

@section('title', 'ዳሽቦርድ')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <article class="kpi-card">
                <div class="kpi-label">ዐቃቤ ህጎች</div>
                <div class="kpi-value">{{ number_format($prosecutors) }}</div>
                <div class="meta">በዚህ እይታ ውስጥ ያሉ ዐቃቤ ህጎች</div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="kpi-card">
                <div class="kpi-label">ፐብሊክ ሰርቫንት</div>
                <div class="kpi-value">{{ number_format($servants) }}</div>
                <div class="meta">በዚህ እይታ ውስጥ ያሉ ፐብሊክ ሰርቫንቶች</div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="kpi-card">
                <div class="kpi-label">የሁለቱ ድምር</div>
                <div class="kpi-value">{{ number_format($combined) }}</div>
                <div class="meta">ዐቃቤ ህግ እና ፐብሊክ ሰርቫንት</div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="kpi-card">
                <div class="kpi-label">ጠቅላላ ህጎች</div>
                <div class="kpi-value">{{ number_format($laws) }}</div>
                <div class="meta">በመምሪያው የተመዘገቡ ህጎች</div>
            </article>
        </div>
    </div>

    <section class="page-card p-3 p-lg-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
            <h2 class="h5 mb-0">{{ $performanceReviewer ? 'ለማጽደቅ የቀረበ አፈጻጸም' : 'የእኔ አፈጻጸም' }}</h2>
            <a href="{{ route('admin.performance.index', $performanceReviewer ? ['status' => 'submitted'] : ['mine' => 1]) }}">ሁሉንም ይመልከቱ</a>
        </div>
        @forelse ($performance as $report)
            <article class="d-flex flex-wrap justify-content-between gap-2 border-bottom py-2">
                <div>
                    <a href="{{ route('admin.performance.show', $report) }}">{{ $report->user?->displayName() }}</a>
                    <div class="meta">
                        {{ $report->organization?->amharicName() }}
                        · {{ $report->period->label() }}
                        · {{ $report->budget_year_ec }}
                        · {{ $report->period->numberLabel() }} {{ $report->period_number }}
                    </div>
                </div>
                <span class="meta">{{ $report->status->label() }}</span>
            </article>
        @empty
            <div class="empty-state mb-0">{{ $performanceReviewer ? 'ለማጽደቅ የቀረበ አፈጻጸም የለም።' : 'እስካሁን ያቀረቡት አፈጻጸም የለም።' }}</div>
        @endforelse
    </section>

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
@endsection
