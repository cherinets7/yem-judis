<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.style')
</head>
<body>
    <h1>የአፈጻጸም ሪፖርት</h1>
    @if ($reports->isEmpty())
        <p>አፈጻጸም አልተገኘም።</p>
    @else
        @foreach ($reports as $report)
            <h2>{{ $report->user?->displayName() }} — {{ $report->period->label() }} {{ $report->period_number }} · {{ $report->budget_year_ec }}</h2>
            <p>{{ $report->organization?->amharicName() }} · {{ $report->status->label() }}</p>
            <p><strong>የታቀደ፡</strong> {{ $report->planned ?: '—' }}</p>
            <p><strong>የተከናወነ፡</strong> {{ $report->accomplished ?: '—' }}</p>
            <p><strong>ፈተና፡</strong> {{ $report->challenges ?: '—' }}</p>
        @endforeach
    @endif
</body>
</html>
