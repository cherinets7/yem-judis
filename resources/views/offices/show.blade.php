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
                @forelse ($office->workUnits as $unit)
                    <form class="d-flex flex-wrap gap-2 mb-2" method="post" action="{{ route('admin.offices.units.update', [$office, $unit]) }}">
                        @csrf
                        @method('PUT')
                        <input class="form-control form-control-sm" name="name" value="{{ $unit->name }}" required>
                        <button class="btn btn-sm btn-outline-success" type="submit">አርም</button>
                    </form>
                    <form class="mb-3" method="post" action="{{ route('admin.offices.units.deactivate', [$office, $unit]) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger" type="submit">{{ $unit->is_active ? 'አጥፋ' : 'አብራ' }}</button>
                        <span class="meta">{{ $unit->is_active ? 'በርቷል' : 'ጠፍቷል' }}</span>
                    </form>
                @empty
                    <div class="empty-state">የስራ ክፍል አልተመዘገበም።</div>
                @endforelse
                <form class="d-flex flex-wrap gap-2" method="post" action="{{ route('admin.offices.units.store', $office) }}">
                    @csrf
                    <input class="form-control form-control-sm @error('name') is-invalid @enderror" name="name" placeholder="አዲስ የስራ ክፍል" required>
                    <button class="btn btn-sm btn-success" type="submit">ጨምር</button>
                </form>
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
