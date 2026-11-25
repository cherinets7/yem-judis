@extends('layouts.public')

@section('title', 'ስለ እኛ')

@section('content')
    <section class="portal-card">
        <h1 class="section-title mb-2">ስለ {{ $setting->department_name }}</h1>
        <p class="post-body mb-0">{{ $setting->about_text ?: 'የየም ዞን ፍትህ መምሪያ ለዜጎች የፍትህ አገልግሎት በዲጂታል መንገድ ያቀርባል።' }}</p>
    </section>
@endsection
