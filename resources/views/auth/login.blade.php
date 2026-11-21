@extends('layouts.public')

@section('title', 'መግቢያ')

@section('content')
    <div class="row justify-content-center py-4">
        <div class="col-md-8 col-lg-5">
            <section class="portal-card p-4">
                <h1 class="h4 mb-1">የሰራተኛ መግቢያ</h1>
                <p class="meta mb-4">የየም ዞን ፍትህ መምሪያ የውስጥ መዝገብ።</p>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="post" action="{{ route('login.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">ኢሜይል</label>
                        <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">የይለፍ ቃል</label>
                        <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                        <label class="form-check-label" for="remember">አስታውሰኝ</label>
                    </div>
                    <button class="btn btn-green w-100" type="submit">ግባ</button>
                </form>

                <p class="meta mt-4 mb-0">የሙከራ መለያዎች በ README ተዘርዝረዋል። የይለፍ ቃል password ነው።</p>
            </section>
        </div>
    </div>
@endsection
