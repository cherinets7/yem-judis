@extends('layouts.app')

@section('title', 'መዝገብ ፍለጋ')

@section('content')
    <h1 class="h4 mb-3">መዝገብ ፍለጋ</h1>
    <form class="page-card p-3 mb-4" method="get" action="{{ route('admin.search') }}">
        <label class="form-label" for="q">ቁጥር፣ ስም ወይም ማጠቃለያ</label>
        <div class="d-flex flex-wrap gap-2">
            <input class="form-control" id="q" name="q" value="{{ $q }}" placeholder="ለምሳሌ YZ/ወ/100/2019" required>
            <button class="btn btn-success" type="submit">ፈልግ</button>
        </div>
        <p class="meta mb-0 mt-2">በጉዳይ፣ ውክልና፣ ውል፣ ማኅበር እና ሌሎች መዝገቦች ላይ በአንድ ጊዜ ይፈልጋል።</p>
    </form>

    @if ($q === '')
        <div class="page-card empty-state">ቁጥር ወይም ስም ያስገቡ።</div>
    @elseif ($groups === [])
        <div class="page-card empty-state">ከዚህ ፍለጋ ጋር የሚዛመድ መዝገብ አልተገኘም።</div>
    @else
        @foreach ($groups as $group)
            <section class="page-card p-3 p-lg-4 mb-3">
                <h2 class="h6">{{ $group['definition']['title'] }}@if (! empty($group['definition']['code'])) · መዝገብ {{ $group['definition']['code'] }}@endif</h2>
                @foreach ($group['records'] as $record)
                    <div class="border-bottom py-2">
                        <a href="{{ route('admin.'.$group['definition']['uri'].'.show', $record) }}">{{ \App\Support\RegisterSearch::title($record, $group['definition']) }}</a>
                        <div class="meta">{{ $record->organization?->amharicName() }}</div>
                    </div>
                @endforeach
            </section>
        @endforeach
    @endif
@endsection
