@extends('layouts.app')

@section('title', $office->amharicName())

@section('content')
    <section class="page-card p-3 p-lg-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <div class="meta">{{ $office->type->label() }} · {{ $office->is_active ? 'በርቷል' : 'ጠፍቷል' }}</div>
                <h1 class="h4 mb-1">{{ $office->amharicName() }}</h1>
                @if ($office->parent)
                    <div>የበላይ ዞን፡ {{ $office->parent->amharicName() }}</div>
                @endif
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-success" href="{{ route('admin.offices.edit', $office) }}">አርም</a>
                <form method="post" action="{{ route('admin.offices.deactivate', $office) }}">
                    @csrf
                    <button class="btn btn-outline-danger" type="submit">{{ $office->is_active ? 'አጥፋ' : 'አብራ' }}</button>
                </form>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-6">
            <section class="page-card p-3 h-100">
                <h2 class="h6">የስራ ክፍሎች</h2>
                @if ($office->workUnits->isEmpty())
                    <div class="empty-state">የስራ ክፍል አልተመዘገበም።</div>
                @else
                    <ul class="mb-0">
                        @foreach ($office->workUnits as $unit)
                            <li>{{ preg_replace('/\s*\([^)]*\)\s*$/u', '', $unit->name) }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
        <div class="col-lg-6">
            <section class="page-card p-3 h-100">
                <h2 class="h6">ባለሙያዎች</h2>
                @if ($office->users->isEmpty())
                    <div class="empty-state">ባለሙያ አልተመዘገበም።</div>
                @else
                    <ul class="mb-0">
                        @foreach ($office->users as $user)
                            <li>{{ $user->displayName() }} · {{ $user->role->label() }}{{ $user->is_active ? '' : ' · ጠፍቷል' }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
        @if ($office->children->isNotEmpty())
            <div class="col-12">
                <section class="page-card p-3">
                    <h2 class="h6">የበታች ወረዳዎች</h2>
                    <ul class="mb-0">
                        @foreach ($office->children as $child)
                            <li><a href="{{ route('admin.offices.show', $child) }}">{{ $child->amharicName() }}</a> · {{ $child->is_active ? 'በርቷል' : 'ጠፍቷል' }}</li>
                        @endforeach
                    </ul>
                </section>
            </div>
        @endif
    </div>
@endsection
