@extends('layouts.public')

@section('title', 'ሌሎች')

@section('content')
    <div class="mb-3">
        <h1 class="section-title mb-1">ሌሎች</h1>
        <p class="meta mb-0">ዜና፣ ማስታወቂያ፣ የህዝብ አስተያየት እና የህግ ቀልድ።</p>
    </div>

    <ul class="nav nav-tabs portal-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link @if($tab === 'news') active @endif" href="{{ route('community.index', ['tab' => 'news']) }}">ዜናዎች</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($tab === 'announcements') active @endif" href="{{ route('community.index', ['tab' => 'announcements']) }}">ማስታወቂያዎች</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($tab === 'feedback') active @endif" href="{{ route('community.index', ['tab' => 'feedback']) }}">አስተያየቶች</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($tab === 'jokes') active @endif" href="{{ route('community.index', ['tab' => 'jokes']) }}">የህግ ቀልዶች</a>
        </li>
    </ul>

    @if ($tab === 'feedback')
        <section class="portal-card mb-4">
            <h2>አስተያየት ያስተላልፉ</h2>
            <p class="meta">ስምዎንና አስተያየትዎን ይጻፉ። አስተያየቱ ከተገመገመ እና ከታተመ በኋላ በዚህ ገጽ ላይ ይታያል።</p>
            @if (session('status'))
                <div class="alert alert-success" role="status">{{ session('status') }}</div>
            @endif
            <form method="post" action="{{ route('community.feedback') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="guest_name">ስም</label>
                        <input class="form-control @error('guest_name') is-invalid @enderror" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" required>
                        @error('guest_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="title">ርዕስ</label>
                        <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="content">አስተያየት</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="4" required>{{ old('content') }}</textarea>
                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <button class="btn btn-gold" type="submit">አስተያየት ላክ</button>
                    </div>
                </div>
            </form>
        </section>
    @endif

    @forelse ($posts as $post)
        <article class="portal-card mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                <span class="badge badge-gold">{{ $post->type->label() }}</span>
                <span class="meta">{{ \App\Helpers\EthiopianDateHelper::fromCarbon($post->created_at) }}</span>
            </div>
            <h2>{{ $post->title }}</h2>
            @if ($post->guest_name)
                <p class="meta mb-2">ከ {{ $post->guest_name }}</p>
            @endif
            <p class="post-body mb-0">{{ $post->content }}</p>
        </article>
    @empty
        <div class="empty-state">
            @if ($tab === 'feedback')
                እስካሁን የታተመ አስተያየት የለም።
            @else
                በዚህ ክፍል ምንም የታተመ ይዘት የለም።
            @endif
        </div>
    @endforelse
@endsection
