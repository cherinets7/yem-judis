@extends('layouts.app')

@section('title', $report->period->label())

@section('content')
    <section class="page-card p-3 p-lg-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <div class="meta">{{ $report->organization?->amharicName() }}</div>
                <h1 class="h4 mb-1">{{ $report->period->label() }} {{ $report->period_number }} · {{ $report->budget_year_ec }}</h1>
                <div>{{ $report->user?->displayName() }} · {{ $report->status->label() }}</div>
                @if ($report->period_label)
                    <div class="meta">{{ $report->period_label }}</div>
                @endif
            </div>
            @if ($report->user_id === auth()->id() && $report->isOpen())
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-success" href="{{ route('admin.performance.edit', $report) }}">አርም</a>
                    <form method="post" action="{{ route('admin.performance.destroy', $report) }}" onsubmit="return confirm('ይህ ረቂቅ ይሰረዝ?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger" type="submit">ሰርዝ</button>
                    </form>
                </div>
            @endif
        </div>

        <h2 class="h6">የታቀደ</h2>
        <p class="post-body">{{ $report->planned ?: '—' }}</p>
        <h2 class="h6">የተከናወነ</h2>
        <p class="post-body">{{ $report->accomplished ?: '—' }}</p>
        <h2 class="h6">ፈተናዎች</h2>
        <p class="post-body">{{ $report->challenges ?: '—' }}</p>

        @if ($report->review_note)
            <div class="alert alert-warning">የግምገማ ማስታወሻ፡ {{ $report->review_note }}</div>
        @endif
        @if ($report->reviewer)
            <div class="meta">የገመገመው፡ {{ $report->reviewer->displayName() }}</div>
        @endif

        @if ($canReview && $report->status === \App\Enums\ReviewStatus::Submitted)
            <div class="d-flex flex-wrap gap-2 mt-4">
                <form method="post" action="{{ route('admin.performance.approve', $report) }}">
                    @csrf
                    <button class="btn btn-success" type="submit">አጽድቅ</button>
                </form>
                <form class="d-flex gap-2" method="post" action="{{ route('admin.performance.reject', $report) }}">
                    @csrf
                    <input class="form-control" name="review_note" placeholder="የግምገማ ማስታወሻ" maxlength="2000">
                    <button class="btn btn-outline-danger" type="submit">አትቀበል</button>
                </form>
            </div>
        @endif
    </section>
@endsection
