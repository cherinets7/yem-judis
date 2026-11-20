@extends('layouts.app')

@section('title', $expert ? 'ባለሙያ አርም' : 'ባለሙያ መዝግብ')

@section('content')
    <section class="page-card p-3 p-lg-4">
        <h1 class="h4 mb-3">{{ $expert ? 'ባለሙያ አርም' : 'ባለሙያ መዝግብ' }}</h1>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="post" action="{{ $expert ? route('admin.experts.update', $expert) : route('admin.experts.store') }}">
            @csrf
            @if ($expert)
                @method('PUT')
            @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">ስም</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name', $expert?->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">ኢሜይል</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $expert?->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">የይለፍ ቃል</label>
                    <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" @unless($expert) required @endunless>
                    @if ($expert)
                        <div class="meta">ባዶ ከተተወ የቀድሞው ይቆያል።</div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="role">ሚና</label>
                    <select class="form-select" id="role" name="role" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', $expert?->role?->value) === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="organization_id">ተቋም</label>
                    <select class="form-select" id="organization_id" name="organization_id" required>
                        <option value="">ምረጥ</option>
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected((string) old('organization_id', $expert?->organization_id) === (string) $organization->id)>{{ $organization->amharicName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="work_unit_id">የስራ ክፍል</label>
                    <select class="form-select" id="work_unit_id" name="work_unit_id" required>
                        @foreach ($workUnits as $unit)
                            <option value="{{ $unit->id }}" data-organization="{{ $unit->organization_id }}" @selected((string) old('work_unit_id', $expert?->work_unit_id) === (string) $unit->id)>{{ preg_replace('/\s*\([^)]*\)\s*$/u', '', $unit->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check">
                        <input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked((string) old('is_active', ($expert?->is_active ?? true) ? '1' : '0') === '1')>
                        <label class="form-check-label" for="is_active">በርቷል</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-success" type="submit">{{ $expert ? 'አዘምን' : 'መዝግብ' }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.experts.index') }}">ተመለስ</a>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        const organization = document.querySelector('#organization_id');
        const units = [...document.querySelectorAll('#work_unit_id option')];
        const sync = () => {
            units.forEach((option) => {
                const visible = option.dataset.organization === organization.value;
                option.hidden = !visible;
                if (!visible && option.selected) {
                    option.selected = false;
                }
            });
        };
        organization.addEventListener('change', sync);
        sync();
    </script>
@endpush
