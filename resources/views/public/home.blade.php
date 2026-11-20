@extends('layouts.public')

@section('title', 'መነሻ')

@section('content')
    <section class="hero mb-5">
        <div>
            <p class="hero-kicker mb-2">የየም ዞን ፍትህ መምሪያ</p>
            <h1>የዞኑ ፍትህ አገልግሎት በአንድ ቦታ</h1>
            <p class="hero-lead">የጉዳይ ሁኔታ በመዝገብ ቁጥር ወይም በስም ይከታተሉ፣ አዋጅና መመሪያ ያውርዱ፣ የመምሪያውን ዜናና ማስታወቂያ ያንብቡ።</p>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <a class="btn btn-gold" href="{{ route('cases.tracking') }}">ጉዳይ ይፈልጉ</a>
                <a class="btn btn-green" href="{{ route('laws.index') }}">ህጎችን ይመልከቱ</a>
                <a class="btn btn-outline-light" href="{{ route('community.index') }}">ዜናና አስተያየት</a>
            </div>
        </div>
        <div class="hero-services" aria-label="ዋና አገልግሎቶች">
            <a href="{{ route('cases.tracking') }}">
                <span>01</span>
                <strong>ጉዳይ መከታተል</strong>
                <small>በመዝገብ ቁጥር ወይም በስም</small>
            </a>
            <a href="{{ route('laws.index') }}">
                <span>02</span>
                <strong>ህጎች</strong>
                <small>አዋጅ፣ ደንብ እና መመሪያ</small>
            </a>
            <a href="{{ route('community.index') }}">
                <span>03</span>
                <strong>ዜናና አስተያየት</strong>
                <small>ማስታወቂያ እና የህዝብ ምላሽ</small>
            </a>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-4">
            <h2 class="section-title">የቅርብ ጊዜ ዜናዎች</h2>
            @forelse ($news as $post)
                <article class="portal-card mb-3">
                    <div class="meta mb-1">{{ \App\Helpers\EthiopianDateHelper::fromCarbon($post->created_at) }}</div>
                    <h3>{{ $post->title }}</h3>
                    <p class="mb-0 post-body">{{ \Illuminate\Support\Str::limit($post->content, 160) }}</p>
                </article>
            @empty
                <div class="empty-state">ምንም የታተመ ዜና የለም።</div>
            @endforelse
            <a href="{{ route('community.index', ['tab' => 'news']) }}">ሁሉንም ዜናዎች ይመልከቱ</a>
        </div>
        <div class="col-lg-4">
            <h2 class="section-title">ማስታወቂያዎች</h2>
            @forelse ($announcements as $post)
                <article class="portal-card mb-3">
                    <div class="meta mb-1">{{ \App\Helpers\EthiopianDateHelper::fromCarbon($post->created_at) }}</div>
                    <h3>{{ $post->title }}</h3>
                    <p class="mb-0 post-body">{{ \Illuminate\Support\Str::limit($post->content, 160) }}</p>
                </article>
            @empty
                <div class="empty-state">ምንም የታተመ ማስታወቂያ የለም።</div>
            @endforelse
            <a href="{{ route('community.index', ['tab' => 'announcements']) }}">ሁሉንም ማስታወቂያዎች ይመልከቱ</a>
        </div>
        <div class="col-lg-4">
            <h2 class="section-title">የህግ ማጠቃለያ</h2>
            @forelse ($laws as $law)
                <article class="portal-card mb-3">
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <span class="badge badge-green">{{ $law->jurisdiction->label() }}</span>
                        <span class="badge badge-gold">{{ $law->category->label() }}</span>
                    </div>
                    <h3>{{ $law->title }}</h3>
                    <p class="meta mb-2">
                        @if ($law->law_number) ቁጥር {{ $law->law_number }} @endif
                        @if ($law->issued_year_ec) · {{ $law->issued_year_ec }} ዓ.ም. @endif
                    </p>
                    <p class="mb-2">{{ \Illuminate\Support\Str::limit($law->description, 140) }}</p>
                    @if ($law->file_path)
                        <a class="btn btn-sm btn-green" href="{{ route('laws.download', $law) }}">አውርድ</a>
                    @endif
                </article>
            @empty
                <div class="empty-state">ምንም ህግ አልተመዘገበም።</div>
            @endforelse
            <a href="{{ route('laws.index') }}">ሙሉ የህግ ዝርዝር</a>
        </div>
    </div>
@endsection
