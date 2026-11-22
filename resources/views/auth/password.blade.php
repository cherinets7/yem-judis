@extends('layouts.app')

@section('title', 'የይለፍ ቃል')

@section('content')
    <section class="page-card p-3 p-lg-4 col-lg-6">
        <h1 class="h4 mb-3">የይለፍ ቃል ቀይር</h1>
        <form method="post" action="{{ route('password.change') }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label" for="current_password">የአሁኑ የይለፍ ቃል</label>
                <input class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">አዲስ የይለፍ ቃል</label>
                <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="new-password" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirmation">አዲሱን ድገም</label>
                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button class="btn btn-success" type="submit">ቀይር</button>
        </form>
    </section>
@endsection
