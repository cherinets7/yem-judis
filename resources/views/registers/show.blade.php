@php use App\Support\FieldPresenter; @endphp

@extends('layouts.app')

@section('title', $definition['title'])

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            @if (! empty($definition['code']))
                <div class="meta">መዝገብ {{ $definition['code'] }}</div>
            @endif
            <h1 class="h4 mb-0">{{ $definition['title'] }}</h1>
        </div>
        <a class="btn btn-outline-success" href="{{ route('admin.'.$definition['uri'].'.index') }}">ወደ ዝርዝር</a>
    </div>

    <section class="page-card p-3 p-lg-4 mb-4">
        <dl class="row mb-3">
            @foreach ($definition['fields'] as $field)
                @continue($field['type'] === 'file')
                <dt class="col-sm-4">{{ $field['label'] }}</dt>
                <dd class="col-sm-8">{{ FieldPresenter::detail($record, $field['name']) }}</dd>
            @endforeach
            @if ($definition['scoped'])
                <dt class="col-sm-4">ተቋም</dt>
                <dd class="col-sm-8">{{ FieldPresenter::detail($record, 'organization_id') }}</dd>
            @endif
        </dl>
        @include('registers.partials.actions', ['record' => $record])
    </section>

    @if ($definition['documents'] ?? false)
        <section class="page-card p-3 p-lg-4 mb-4">
            <h2 class="h6">የተያያዙ ፋይሎች</h2>
            <p class="meta">PDF፣ ምስል ወይም ዎርድ እስከ 10 ሜጋ ያያይዙ።</p>
            @forelse ($record->documents as $document)
                <div class="d-flex flex-wrap justify-content-between gap-2 border-bottom py-2">
                    <div>
                        <a href="{{ route('admin.'.$definition['uri'].'.documents.download', [$record, $document]) }}">{{ $document->title }}</a>
                        <div class="meta">{{ $document->uploader?->displayName() }}</div>
                    </div>
                    <form method="post" action="{{ route('admin.'.$definition['uri'].'.documents.destroy', [$record, $document]) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">አውጣ</button>
                    </form>
                </div>
            @empty
                <div class="empty-state">እስካሁን ፋይል አልተያያዘም።</div>
            @endforelse
            <form class="row g-2 align-items-end mt-3" method="post" action="{{ route('admin.'.$definition['uri'].'.documents.store', $record) }}" enctype="multipart/form-data">
                @csrf
                <div class="col-md-5">
                    <label class="form-label" for="title">የፋይል ርዕስ</label>
                    <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="file">ፋይል</label>
                    <input class="form-control @error('file') is-invalid @enderror" id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100" type="submit">ያያይዙ</button>
                </div>
            </form>
        </section>
    @endif

    <section class="page-card p-3 p-lg-4">
        <h2 class="h6">የለውጥ ታሪክ</h2>
        @forelse ($audits as $log)
            <div class="border-bottom py-2">
                <div>{{ $log->summary }}</div>
                <div class="meta">{{ $log->user?->displayName() ?: 'ሥርዓት' }} · {{ \App\Helpers\EthiopianDateHelper::fromCarbon($log->created_at) }}</div>
            </div>
        @empty
            <div class="empty-state mb-0">የተመዘገበ ለውጥ የለም።</div>
        @endforelse
    </section>
@endsection
