@extends('layouts.app')

@section('title', 'ዞን እና ወረዳ')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">ዞን እና ወረዳ</h1>
        <a class="btn btn-success" href="{{ route('admin.offices.create') }}">አዲስ መዝግብ</a>
    </div>

    @if ($offices->isEmpty())
        <div class="page-card empty-state">የተመዘገበ ተቋም የለም።</div>
    @else
        <div class="page-card p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ስም</th>
                            <th>አይነት</th>
                            <th>ዞን</th>
                            <th>ሁኔታ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($offices as $office)
                            <tr>
                                <td>{{ $office->amharicName() }}</td>
                                <td>{{ $office->type->label() }}</td>
                                <td>{{ $office->parent?->amharicName() ?? '—' }}</td>
                                <td>{{ $office->is_active ? 'በርቷል' : 'ጠፍቷል' }}</td>
                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-outline-success" href="{{ route('admin.offices.show', $office) }}">ይመልከቱ</a>
                                    <a class="btn btn-sm btn-outline-success" href="{{ route('admin.offices.edit', $office) }}">አርም</a>
                                    <form class="d-inline" method="post" action="{{ route('admin.offices.deactivate', $office) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" type="submit">{{ $office->is_active ? 'አጥፋ' : 'አብራ' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
