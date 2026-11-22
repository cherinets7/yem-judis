@extends('layouts.public')

@section('title', 'የይለፍ ቃል መልስ')

@section('content')
    <div class="row justify-content-center py-4">
        <div class="col-md-8 col-lg-5">
            <section class="portal-card p-4">
                <h1 class="h4 mb-1">የይለፍ ቃል ረስተዋል?</h1>
                <p class="meta mb-4">ኢሜይልዎን ይጻፉ። የመቀየሪያ አገናኝ እንልካለን።</p>
                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif
                <form method="post" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">ኢሜይል</label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button class="btn btn-green w-100" type="submit">አገናኝ ላክ</button>
                </form>
                <p class="mt-3 mb-0"><a href="{{ route('login') }}">ወደ መግቢያ ተመለስ</a></p>
            </section>
        </div>
    </div>
@endsection
