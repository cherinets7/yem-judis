@extends('layouts.app')

@section('title', $expert->displayName())

@section('content')
    <section class="page-card p-3 p-lg-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <div class="meta">{{ $expert->role->label() }} · {{ $expert->is_active ? 'በርቷል' : 'ጠፍቷል' }}</div>
                <h1 class="h4 mb-1">{{ $expert->displayName() }}</h1>
                <div>{{ $expert->email }}</div>
                <div>{{ $expert->organization?->amharicName() }}</div>
                <div>{{ preg_replace('/\s*\([^)]*\)\s*$/u', '', (string) $expert->workUnit?->name) }}</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-success" href="{{ route('admin.experts.edit', $expert) }}">አርም</a>
                @unless ($expert->is(auth()->user()))
                    <form method="post" action="{{ route('admin.experts.deactivate', $expert) }}">
                        @csrf
                        @if ($expert->is_active && collect($links)->sum('count') > 0)
                            <select class="form-select form-select-sm mb-2" name="transfer_to" required>
                                <option value="">ጉዳዩን የሚረከብ</option>
                                @foreach ($successors as $successor)
                                    <option value="{{ $successor->id }}">{{ $successor->displayName() }}</option>
                                @endforeach
                            </select>
                        @endif
                        <button class="btn btn-outline-danger" type="submit">{{ $expert->is_active ? 'አጥፋ' : 'አብራ' }}</button>
                    </form>
                @endunless
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-6">
            <section class="page-card p-3 h-100">
                <h2 class="h6">የሰው ሀብት መገለጫ</h2>
                @if ($expert->staffProfile)
                    <p class="mb-0"><a href="{{ route('admin.staff-profiles.edit', $expert->staffProfile) }}">{{ $expert->staffProfile->full_name }}</a> · {{ $expert->staffProfile->staff_category->label() }}</p>
                @else
                    <div class="empty-state">ከመግቢያ መለያ ጋር የተያያዘ መገለጫ የለም።</div>
                @endif
            </section>
        </div>
        <div class="col-lg-6">
            <section class="page-card p-3 h-100">
                <h2 class="h6">የተሳሰሩ መዝገቦች</h2>
                <ul class="mb-0">
                    @foreach ($links as $link)
                        <li>{{ $link['label'] }}፡ {{ $link['count'] }}</li>
                    @endforeach
                </ul>
            </section>
        </div>
        @unless ($expert->is(auth()->user()))
            <div class="col-lg-6">
                <section class="page-card p-3 h-100">
                    <h2 class="h6">የይለፍ ቃል ቀይር</h2>
                    <p class="meta">ኢሜይሉ ካልደረሰ ኃላፊ ወይም የሰው ሀብት ከዚህ ይቀይራል።</p>
                    <form method="post" action="{{ route('admin.experts.password', $expert) }}">
                        @csrf
                        <label class="form-label" for="password">አዲስ የይለፍ ቃል</label>
                        <input class="form-control mb-2 @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <label class="form-label" for="password_confirmation">አዲሱን ድገም</label>
                        <input class="form-control mb-2" id="password_confirmation" name="password_confirmation" type="password" required>
                        <button class="btn btn-success" type="submit">ቀይር</button>
                    </form>
                </section>
            </div>
        @endunless
        <div class="col-lg-6">
            <section class="page-card p-3 h-100">
                <h2 class="h6">አፈጻጸም</h2>
                @if ($expert->performanceReports->isEmpty())
                    <div class="empty-state">እስካሁን አፈጻጸም አላቀረበም።</div>
                @else
                    <ul class="mb-0">
                        @foreach ($expert->performanceReports->sortByDesc('id') as $report)
                            <li>{{ $report->period->label() }} {{ $report->period_number }} · {{ $report->budget_year_ec }} · {{ $report->status->label() }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endsection
