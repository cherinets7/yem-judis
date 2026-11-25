@extends('layouts.app')

@section('title', 'ባለሙያዎች')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">ባለሙያዎች</h1>
        <a class="btn btn-success" href="{{ route('admin.experts.create') }}">አዲስ ባለሙያ</a>
    </div>

    <form class="page-card p-3 mb-3" method="get" action="{{ route('admin.experts.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label" for="q">ፈልግ</label>
                <input class="form-control" id="q" name="q" value="{{ $q }}" placeholder="ስም ወይም ኢሜይል">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-success" type="submit">ፈልግ</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.experts.index') }}">አጽዳ</a>
            </div>
        </div>
    </form>

    @if ($experts->isEmpty())
        <div class="page-card empty-state">ባለሙያ አልተገኘም።</div>
    @else
        <div class="page-card p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ስም</th>
                            <th>ሚና</th>
                            <th>ተቋም</th>
                            <th>ሁኔታ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($experts as $expert)
                            <tr>
                                <td>{{ $expert->displayName() }}</td>
                                <td>{{ $expert->role->label() }}</td>
                                <td>{{ $expert->organization?->amharicName() }}</td>
                                <td>{{ $expert->is_active ? 'በርቷል' : 'ጠፍቷል' }}</td>
                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-outline-success" href="{{ route('admin.experts.show', $expert) }}">ይመልከቱ</a>
                                    <a class="btn btn-sm btn-outline-success" href="{{ route('admin.experts.edit', $expert) }}">አርም</a>
                                    @unless ($expert->is(auth()->user()))
                                        <form class="d-inline" method="post" action="{{ route('admin.experts.deactivate', $expert) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" type="submit">{{ $expert->is_active ? 'አጥፋ' : 'አብራ' }}</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $experts->links() }}</div>
        </div>
    @endif
@endsection
