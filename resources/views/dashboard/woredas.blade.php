@extends('layouts.app')

@section('title', 'ወረዳዎች')

@section('content')
    <section class="page-card p-3 p-lg-4">
        <h2 class="h5 mb-3">ወረዳዎች እና ከተሞች</h2>
        @if ($offices->isEmpty())
            <div class="empty-state">በዚህ እይታ ውስጥ ጽህፈት ቤት አልተገኘም።</div>
        @else
            <div class="row g-3">
                @foreach ($offices as $office)
                    <div class="col-md-6 col-xl-4">
                        <article class="staff-card p-3 h-100">
                            <div class="meta">{{ $office->type->label() }}</div>
                            <h3 class="h6 mt-1">{{ $office->amharicName() }}</h3>
                            @if ($office->workUnits->isEmpty())
                                <div class="meta">የስራ ክፍል አልተመዘገበም።</div>
                            @else
                                <ul class="mb-0">
                                    @foreach ($office->workUnits as $unit)
                                        <li>{{ preg_replace('/\s*\([^)]*\)\s*$/u', '', $unit->name) }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
