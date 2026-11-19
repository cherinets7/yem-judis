@php
    $hasFile = collect($definition['fields'])->contains(fn ($field) => $field['type'] === 'file');
@endphp

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-bold mb-1">ማስተካከል ያለባቸው ቦታዎች አሉ።</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ $record ? route('admin.'.$definition['uri'].'.update', $record) : route('admin.'.$definition['uri'].'.store') }}" @if($hasFile) enctype="multipart/form-data" @endif>
    @csrf
    @if ($record)
        @method('PUT')
    @endif

    <div class="page-card p-3 p-lg-4">
        <div class="row g-3">
            @if ($definition['scoped'])
                <div class="col-md-6">
                    <label class="form-label" for="organization_id">ተቋም</label>
                    @if ($organizations->count() <= 1)
                        <input type="hidden" name="organization_id" value="{{ old('organization_id', $record?->organization_id ?? $organizations->first()?->id) }}">
                        <div class="form-control-plaintext">{{ $organizations->first()?->amharicName() ?? '—' }}</div>
                    @else
                        <select class="form-select" id="organization_id" name="organization_id" required>
                            <option value="">ምረጥ</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}" @selected((string) old('organization_id', $record?->organization_id ?? '') === (string) $organization->id)>{{ $organization->amharicName() }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            @endif

            @foreach ($definition['fields'] as $field)
                @include('registers.partials.field', ['field' => $field])
            @endforeach
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button class="btn btn-success" type="submit">{{ $record ? 'አዘምን' : 'መዝግብ' }}</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.'.$definition['uri'].'.index') }}">ተመለስ</a>
        </div>
    </div>
</form>

@once
    @push('scripts')
        <script>
            function filterWorkUnits() {
                const organization = document.querySelector('[name="organization_id"]');
                const organizationId = organization ? String(organization.value || '') : '';
                document.querySelectorAll('[data-work-unit]').forEach(function (select) {
                    Array.from(select.options).forEach(function (option) {
                        if (!option.value) {
                            return;
                        }
                        const visible = organizationId === '' || option.dataset.organization === organizationId;
                        option.hidden = !visible;
                        if (!visible && option.selected) {
                            option.selected = false;
                        }
                    });
                });
            }

            document.addEventListener('change', function (event) {
                if (event.target && event.target.name === 'organization_id') {
                    filterWorkUnits();
                }
            });

            let rowSerial = Date.now();
            document.addEventListener('click', function (event) {
                const add = event.target.closest('[data-add-row]');
                if (add) {
                    const editor = add.closest('[data-row-editor]');
                    const list = editor.querySelector('[data-rows]');
                    rowSerial += 1;
                    const html = editor.querySelector('template').innerHTML.replaceAll('__INDEX__', String(rowSerial));
                    list.insertAdjacentHTML('beforeend', html);
                }
                const remove = event.target.closest('[data-remove-row]');
                if (remove) {
                    remove.closest('[data-row]').remove();
                }
            });

            filterWorkUnits();
        </script>
    @endpush
@endonce
