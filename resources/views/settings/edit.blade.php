@extends('layouts.app')

@section('title', 'ቅንብሮች')

@section('content')
    <section class="page-card p-3 p-lg-4">
        <h1 class="h4 mb-1">ቅንብሮች</h1>
        <p class="meta mb-4">የመምሪያው ስም፣ አድራሻ እና የፉተር ጽሑፍ በህዝብ በርም በሰራተኛ ገጽም ይታያል።</p>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="post" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="department_name">የመምሪያ ስም</label>
                    <input class="form-control" id="department_name" name="department_name" value="{{ old('department_name', $setting->department_name) }}" @disabled(! $canEdit) required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="address">አድራሻ</label>
                    <input class="form-control" id="address" name="address" value="{{ old('address', $setting->address) }}" @disabled(! $canEdit) required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="phone">ስልክ</label>
                    <input class="form-control" id="phone" name="phone" value="{{ old('phone', $setting->phone) }}" @disabled(! $canEdit)>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">ኢሜይል</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $setting->email) }}" @disabled(! $canEdit)>
                </div>
                <div class="col-12">
                    <label class="form-label" for="footer_note">የፉተር ማስታወሻ</label>
                    <textarea class="form-control" id="footer_note" name="footer_note" rows="3" @disabled(! $canEdit) required>{{ old('footer_note', $setting->footer_note) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="about_text">ስለ እኛ ጽሑፍ</label>
                    <textarea class="form-control" id="about_text" name="about_text" rows="3" @disabled(! $canEdit)>{{ old('about_text', $setting->about_text) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="services_text">አገልግሎቶች (እያንዳንዱ መስመር አንድ)</label>
                    <textarea class="form-control" id="services_text" name="services_text" rows="4" @disabled(! $canEdit)>{{ old('services_text', $setting->services_text) }}</textarea>
                </div>
            </div>
            @if ($canEdit)
                <button class="btn btn-success mt-4" type="submit">አስቀምጥ</button>
            @else
                <p class="meta mt-4 mb-0">ይህን ቅንብር የማስተካከል ፈቃድ የለዎትም።</p>
            @endif
        </form>
    </section>
@endsection
