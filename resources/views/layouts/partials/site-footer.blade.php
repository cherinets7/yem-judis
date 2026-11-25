@php
    $siteSetting = \App\Models\Setting::current();
@endphp
<div class="site-footer-grid">
    <div>
        <strong>{{ $siteSetting->department_name }}</strong>
        <div>{{ $siteSetting->address }}</div>
        <div class="d-flex flex-wrap gap-3 mt-2">
            <a href="{{ route('about') }}">ስለ እኛ</a>
            <a href="{{ route('services') }}">አገልግሎቶች</a>
            <a href="{{ route('laws.index') }}">ህጎች</a>
            <a href="{{ route('community.index', ['tab' => 'feedback']) }}">አስተያየት</a>
            <a href="{{ route('contacts') }}">አድራሻ</a>
        </div>
    </div>
    <div>
        @if ($siteSetting->phone)
            <div>ስልክ፡ {{ $siteSetting->phone }}</div>
        @endif
        @if ($siteSetting->email)
            <div>ኢሜይል፡ {{ $siteSetting->email }}</div>
        @endif
        <div>{{ $siteSetting->footer_note }}</div>
    </div>
</div>
