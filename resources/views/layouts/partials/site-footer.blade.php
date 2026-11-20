@php
    $siteSetting = \App\Models\Setting::current();
@endphp
<div class="site-footer-grid">
    <div>
        <strong class="text-dark">{{ $siteSetting->department_name }}</strong>
        <div>{{ $siteSetting->address }}</div>
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
