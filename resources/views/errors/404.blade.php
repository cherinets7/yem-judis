@extends('layouts.public')

@section('title', 'ገጹ አልተገኘም')

@section('content')
    <section class="portal-card p-4 my-4 text-center">
        <h1 class="h4">ገጹ አልተገኘም።</h1>
        <p class="meta">የጠየቁት አድራሻ የለም፣ ወይም ከእርስዎ እይታ ውጭ ነው።</p>
        <a class="btn btn-success" href="{{ route('home') }}">ወደ መነሻ</a>
    </section>
@endsection
