@extends('layouts.public')

@section('title', 'ሁለት ደረጃ ማረጋገጥ')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <section class="portal-card p-4">
                <h1 class="h4 mb-1">ሁለት ደረጃ ማረጋገጥ</h1>
                <p class="meta">ከስልክዎ ወይም ከመልሶ ማግኛ ኮድ ያስገቡ።</p>
                <form method="post" action="{{ route('two-factor.confirm') }}">
                    @csrf
                    <label class="form-label" for="code">ኮድ</label>
                    <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" required>
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <button class="btn btn-green w-100 mt-3" type="submit">አረጋግጥ</button>
                </form>
            </section>
        </div>
    </div>
@endsection
