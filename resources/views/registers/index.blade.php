@php
    use App\Enums\CourtLevel;
    use App\Enums\ReviewStatus;
    use App\Support\FieldPresenter;
@endphp

@extends('layouts.app')

@section('title', $definition['title'])

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            @if (! empty($definition['code']))
                <div class="meta">መዝገብ {{ $definition['code'] }}</div>
            @endif
            <h2 class="h4 mb-0">{{ $definition['title'] }}</h2>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if (! empty($definition['code']) || $definition['key'] === 'staff-profiles')
                <a class="btn btn-outline-success" href="{{ route('admin.exports.pdf', array_merge(['key' => $definition['key']], request()->only(['q', 'court_level']))) }}">PDF ወርድ</a>
                <a class="btn btn-outline-success" href="{{ route('admin.exports.excel', array_merge(['key' => $definition['key']], request()->only(['q', 'court_level']))) }}">ኤክሴል ወርድ</a>
            @endif
            <a class="btn btn-success" href="{{ route('admin.'.$definition['uri'].'.create') }}">አዲስ መዝግብ</a>
        </div>
    </div>

    <form class="page-card p-3 mb-3" method="get" action="{{ route('admin.'.$definition['uri'].'.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label" for="q">ፈልግ</label>
                <input class="form-control" id="q" name="q" value="{{ $q }}" placeholder="ስም፣ ቁጥር ወይም ማጠቃለያ">
            </div>
            @if (request()->filled('court_level'))
                <input type="hidden" name="court_level" value="{{ request('court_level') }}">
            @endif
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-success" type="submit">ፈልግ</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.'.$definition['uri'].'.index') }}">አጽዳ</a>
            </div>
        </div>
    </form>

    @php
        $courtField = collect($definition['fields'])->firstWhere('name', 'court_level');
    @endphp
    @if ($courtField)
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a class="btn btn-sm {{ request()->filled('court_level') ? 'btn-outline-success' : 'btn-success' }}" href="{{ route('admin.'.$definition['uri'].'.index', array_filter(['q' => $q])) }}">ሁሉም</a>
            @foreach (CourtLevel::cases() as $level)
                @php
                    $code = match (true) {
                        $definition['key'] === 'criminal-cases' && $level === CourtLevel::WoredaCourt => '001',
                        $definition['key'] === 'criminal-cases' && $level === CourtLevel::HighCourt => '002',
                        $definition['key'] === 'civil-cases' && $level === CourtLevel::HighCourt => '004',
                        $definition['key'] === 'civil-cases' && $level === CourtLevel::WoredaCourt => '005',
                        default => null,
                    };
                @endphp
                <a class="btn btn-sm {{ request('court_level') === $level->value ? 'btn-success' : 'btn-outline-success' }}" href="{{ route('admin.'.$definition['uri'].'.index', array_filter(['q' => $q, 'court_level' => $level->value])) }}">
                    @if ($code) {{ $code }} @endif{{ $level->label() }}
                </a>
            @endforeach
        </div>
    @endif

    @if ($records->isEmpty())
        <div class="page-card empty-state">አሁን የተመዘገበ መረጃ የለም።</div>
    @else
        <div class="d-lg-none">
            @foreach ($records as $record)
                <article class="staff-card p-3 mb-3">
                    @foreach ($columns as $column)
                        <div class="mb-1"><span class="meta">{{ FieldPresenter::heading($definition, $column) }}፡ </span>{{ FieldPresenter::show($record, $column) }}</div>
                    @endforeach
                    <div class="mt-2">@include('registers.partials.actions', ['record' => $record])</div>
                </article>
            @endforeach
        </div>

        <div class="page-card d-none d-lg-block table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ FieldPresenter::heading($definition, $column) }}</th>
                        @endforeach
                        <th>እርምጃ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            @foreach ($columns as $column)
                                <td>{{ FieldPresenter::show($record, $column) }}</td>
                            @endforeach
                            <td>@include('registers.partials.actions', ['record' => $record])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('public.partials.pager', ['paginator' => $records])
    @endif
@endsection
