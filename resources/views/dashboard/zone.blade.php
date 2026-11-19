@extends('layouts.app')

@section('title', 'ዞን ማዕከል')

@section('content')
    @if (! $zone)
        <div class="page-card empty-state">የዞን መምሪያ አልተገኘም።</div>
    @else
        <section class="page-card p-3 p-lg-4 mb-4">
            <h2 class="h4">{{ $zone->amharicName() }}</h2>
            <p class="meta mb-3">{{ $zone->type->label() }} · {{ $zone->children->count() }} ጽህፈት ቤቶች</p>
            <h3 class="h6">የስራ ክፍሎች</h3>
            @if ($zone->workUnits->isEmpty())
                <div class="empty-state">የስራ ክፍል አልተመዘገበም።</div>
            @else
                <ul class="mb-0">
                    @foreach ($zone->workUnits as $unit)
                        <li>{{ preg_replace('/\s*\([^)]*\)\s*$/u', '', $unit->name) }}</li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="page-card p-3 p-lg-4">
            <h2 class="h5">የበታች ጽህፈት ቤቶች</h2>
            @if ($zone->children->isEmpty())
                <div class="empty-state">የበታች ጽህፈት ቤት አልተመዘገበም።</div>
            @else
                <div class="row g-3">
                    @foreach ($zone->children as $office)
                        <div class="col-md-6">
                            <article class="staff-card p-3 h-100">
                                <div class="fw-bold">{{ $office->amharicName() }}</div>
                                <div class="meta mb-2">{{ $office->type->label() }}</div>
                                <div>{{ $office->workUnits->count() }} የስራ ክፍሎች</div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endif
@endsection
