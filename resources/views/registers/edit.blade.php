@extends('layouts.app')

@section('title', $definition['title'].' አርም')

@section('content')
    <h2 class="h4 mb-3">{{ $definition['title'] }} አርም</h2>
    @include('registers.partials.form')
@endsection
