@extends('layouts.app')

@section('title', 'አዲስ '.$definition['title'])

@section('content')
    <h2 class="h4 mb-3">አዲስ {{ $definition['title'] }}</h2>
    @include('registers.partials.form')
@endsection
