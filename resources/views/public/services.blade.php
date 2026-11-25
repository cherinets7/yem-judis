@extends('layouts.public')

@section('title', 'አገልግሎቶች')

@section('content')
    <section class="portal-card">
        <h1 class="section-title mb-2">አገልግሎቶች</h1>
        <p class="meta mb-3">ከህዝብ በር እና ከሰራተኛ ገጽ የሚገኙ ዋና አገልግሎቶች።</p>
        <ul class="mb-0 ps-3">
            @foreach (preg_split("/\r\n|\n|\r/", (string) ($setting->services_text ?: "የጉዳይ መከታተያ\nየህግ ማውጫና ማውረድ\nየህዝብ አስተያየትና ማስታወቂያ")) as $line)
                @if (trim($line) !== '')
                    <li class="mb-2">{{ trim($line) }}</li>
                @endif
            @endforeach
        </ul>
    </section>
@endsection
