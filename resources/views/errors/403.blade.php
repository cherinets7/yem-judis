@extends('layouts.public')

@section('title', 'ፈቃድ የለም')

@section('content')
    <section class="portal-card p-4 my-4 text-center">
        <h1 class="h4">ይህን ገጽ የመጠቀም ፈቃድ የለዎትም።</h1>
        <p class="meta">ንቁ መለያ እና የሚገባው ሚና ያስፈልጋል።</p>
        <a class="btn btn-success" href="{{ route('home') }}">ወደ መነሻ</a>
    </section>
@endsection
