@extends('layouts.app')

@section('title', $office ? 'ተቋም አርም' : 'ተቋም መዝግብ')

@section('content')
    <section class="page-card p-3 p-lg-4">
        <h1 class="h4 mb-3">{{ $office ? 'ተቋም አርም' : 'ዞን ወይም ወረዳ መዝግብ' }}</h1>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="post" action="{{ $office ? route('admin.offices.update', $office) : route('admin.offices.store') }}">
            @csrf
            @if ($office)
                @method('PUT')
            @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">ስም</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name', $office?->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="type">አይነት</label>
                    <select class="form-select" id="type" name="type" required>
                        @if ($canCreateZone)
                            <option value="zone" @selected(old('type', $office?->type?->value) === 'zone')>ዞን</option>
                        @endif
                        <option value="woreda" @selected(old('type', $office?->type?->value ?? 'woreda') === 'woreda')>ወረዳ</option>
                        <option value="city" @selected(old('type', $office?->type?->value) === 'city')>ከተማ</option>
                    </select>
                </div>
                <div class="col-md-6" data-parent-field>
                    <label class="form-label" for="parent_id">የበላይ ዞን</label>
                    <select class="form-select" id="parent_id" name="parent_id">
                        <option value="">ምረጥ</option>
                        @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}" @selected((string) old('parent_id', $office?->parent_id) === (string) $zone->id)>{{ $zone->amharicName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check">
                        <input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked((string) old('is_active', ($office?->is_active ?? true) ? '1' : '0') === '1')>
                        <label class="form-check-label" for="is_active">በርቷል</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-success" type="submit">{{ $office ? 'አዘምን' : 'መዝግብ' }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.offices.index') }}">ተመለስ</a>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        const type = document.querySelector('#type');
        const parent = document.querySelector('[data-parent-field]');
        const sync = () => { parent.hidden = type.value === 'zone'; };
        type.addEventListener('change', sync);
        sync();
    </script>
@endpush
