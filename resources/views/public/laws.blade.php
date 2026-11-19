@extends('layouts.public')

@section('title', 'ህጎች')

@section('content')
    <div class="mb-4">
        <h1 class="section-title mb-1">ህጎች</h1>
        <p class="meta mb-0">በስልጣንና በምድብ ያጣሩ። ፌዴራል፣ ክልል እና ዞናዊ አዋጅ፣ ደንብ፣ መመሪያና ማኑዋል።</p>
    </div>

    <form class="filter-bar mb-4" method="get" action="{{ route('laws.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="jurisdiction">ስልጣን</label>
                <select class="form-select" id="jurisdiction" name="jurisdiction">
                    <option value="">ሁሉም</option>
                    @foreach ($jurisdictions as $item)
                        <option value="{{ $item->value }}" @selected($jurisdiction === $item->value)>{{ $item->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label" for="category">ምድብ</label>
                <select class="form-select" id="category" name="category">
                    <option value="">ሁሉም</option>
                    @foreach ($categories as $item)
                        <option value="{{ $item->value }}" @selected($category === $item->value)>{{ $item->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-green" type="submit">አጣራ</button>
            </div>
        </div>
    </form>

    @if ($laws->isEmpty())
        <div class="empty-state">ከዚህ ማጣሪያ ጋር የሚዛመድ ህግ አልተገኘም።</div>
    @else
        <div class="row g-3">
            @foreach ($laws as $law)
                <div class="col-md-6 col-xl-4">
                    <article class="portal-card d-flex flex-column">
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <span class="badge badge-green">{{ $law->jurisdiction->label() }}</span>
                            <span class="badge badge-gold">{{ $law->category->label() }}</span>
                        </div>
                        <h2>{{ $law->title }}</h2>
                        <p class="meta">
                            @if ($law->law_number) ቁጥር {{ $law->law_number }} @endif
                            @if ($law->issued_year_ec) · የወጣበት {{ $law->issued_year_ec }} ዓ.ም. @endif
                        </p>
                        <p class="post-body flex-grow-1">{{ $law->description }}</p>
                        @if ($law->file_path)
                            <a class="btn btn-green mt-2" href="{{ route('laws.download', $law) }}">አውርድ</a>
                        @else
                            <span class="meta">የሚወርድ ፋይል አልተያያዘም።</span>
                        @endif
                    </article>
                </div>
            @endforeach
        </div>
        @include('public.partials.pager', ['paginator' => $laws])
    @endif
@endsection
