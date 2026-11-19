@extends('layouts.public')

@section('title', 'ገጹ ጊዜው አልፎበታል')

@section('content')
    <section class="portal-card p-4 my-4 text-center">
        <h1 class="h4">ገጹ ጊዜው አልፎበታል።</h1>
        <p class="meta">ቅጹን እንደገና ይክፈቱ እና ይሞክሩ።</p>
        <a class="btn btn-success" href="{{ route('home') }}">ወደ መነሻ</a>
    </section>
@endsection
