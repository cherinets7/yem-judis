@extends('layouts.public')

@section('title', 'አድራሻ')

@section('content')
    <section class="portal-card">
        <h1 class="section-title mb-2">አድራሻ እና ግንኙነት</h1>
        <p class="mb-2"><strong>{{ $setting->department_name }}</strong></p>
        <p class="mb-2">{{ $setting->address }}</p>
        @if ($setting->phone)
            <p class="mb-2">ስልክ፡ {{ $setting->phone }}</p>
        @endif
        @if ($setting->email)
            <p class="mb-2">ኢሜይል፡ <a href="mailto:{{ $setting->email }}">{{ $setting->email }}</a></p>
        @endif
        <p class="meta mb-0">{{ $setting->footer_note }}</p>
    </section>
@endsection
