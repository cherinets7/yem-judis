@extends('layouts.app')

@section('title', 'የለውጥ ታሪክ')

@section('content')
    <h1 class="h4 mb-3">የለውጥ ታሪክ</h1>
    <section class="page-card p-3 p-lg-4">
        @if ($logs->isEmpty())
            <div class="empty-state mb-0">የተመዘገበ ለውጥ የለም።</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ለውጥ</th>
                            <th>ሰው</th>
                            <th>ተቋም</th>
                            <th>ቀን</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>{{ $log->summary }}</td>
                                <td>{{ $log->user?->displayName() ?: '—' }}</td>
                                <td>{{ $log->organization?->amharicName() ?: '—' }}</td>
                                <td>{{ \App\Helpers\EthiopianDateHelper::fromCarbon($log->created_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">@include('public.partials.pager', ['paginator' => $logs])</div>
        @endif
    </section>
@endsection
