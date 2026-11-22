@extends('layouts.public')

@section('title', 'አዲስ የይለፍ ቃል')

@section('content')
    <div class="row justify-content-center py-4">
        <div class="col-md-8 col-lg-5">
            <section class="portal-card p-4">
                <h1 class="h4 mb-3">አዲስ የይለፍ ቃል</h1>
                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="mb-3">
                        <label class="form-label" for="email">ኢሜይል</label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $email) }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">አዲስ የይለፍ ቃል</label>
                        <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">ድገም</label>
                        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
                    </div>
                    <button class="btn btn-green w-100" type="submit">ቀይር</button>
                </form>
            </section>
        </div>
    </div>
@endsection
