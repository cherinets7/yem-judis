@extends('layouts.app')

@section('title', 'ሁለት ደረጃ ማረጋገጥ')

@section('content')
    <h1 class="h4 mb-3">ሁለት ደረጃ ማረጋገጥ</h1>

    @if ($enabled)
        <section class="page-card p-3 p-lg-4 mb-4">
            <p class="mb-3">ሁለት ደረጃ ማረጋገጥ በርቷል።</p>
            <form method="post" action="{{ route('two-factor.disable') }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger" type="submit">አጥፋ</button>
            </form>
        </section>
    @else
        <section class="page-card p-3 p-lg-4 mb-4">
            <p class="meta">Authenticator መተግበሪያ ላይ ይህን ባር ኮድ ያንቁ ወይም ምስጢሩን ያስገቡ።</p>
            @if ($qr)
                <div class="mb-3">{!! $qr !!}</div>
            @endif
            @if ($secret)
                <div class="mb-3"><code>{{ $secret }}</code></div>
            @endif
            <form method="post" action="{{ route('two-factor.enable') }}">
                @csrf
                <label class="form-label" for="code">ከመተግበሪያው የመጣ ኮድ</label>
                <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" required>
                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <button class="btn btn-success mt-3" type="submit">አብራ</button>
            </form>
        </section>
    @endif

    @if (! empty($recovery))
        <section class="page-card p-3 p-lg-4">
            <h2 class="h6">የመልሶ ማግኛ ኮዶች</h2>
            <p class="meta">አንድ ጊዜ ብቻ ይታያሉ። በደህንነት ያስቀምጡ።</p>
            <ul class="mb-0">
                @foreach ($recovery as $code)
                    <li><code>{{ $code }}</code></li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
