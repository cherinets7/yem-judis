@php
    $user = auth()->user();
@endphp
<nav class="staff-nav" aria-label="የሰራተኛ ዝርዝር">
    <a class="staff-link @if(request()->routeIs('dashboard')) is-active @endif" href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>ዳሽቦርድ</a>

    @if (\App\Support\Roles::allows($user, \App\Support\Roles::ZONE))
        <a class="staff-link @if(request()->routeIs('zone-center')) is-active @endif" href="{{ route('zone-center') }}" @if(request()->routeIs('zone-center')) aria-current="page" @endif>ዞን ማዕከል</a>
    @endif

    <a class="staff-link @if(request()->routeIs('woredas')) is-active @endif" href="{{ route('woredas') }}" @if(request()->routeIs('woredas')) aria-current="page" @endif>ወረዳዎች</a>

    @foreach (\App\Support\RegisterCatalog::sections() as $section)
        @continue(! \App\Support\Roles::allows($user, $section['roles']))
        @if (count($section['keys']) === 1)
            @php $definition = \App\Support\RegisterCatalog::get($section['keys'][0]); @endphp
            <a class="staff-link @if(request()->routeIs('admin.'.$definition['uri'].'.*')) is-active @endif" href="{{ route('admin.'.$definition['uri'].'.index') }}" @if(request()->routeIs('admin.'.$definition['uri'].'.*')) aria-current="page" @endif>{{ $section['heading'] }}</a>
        @else
            <div class="staff-heading">{{ $section['heading'] }}</div>
            @foreach ($section['keys'] as $key)
                @php $definition = \App\Support\RegisterCatalog::get($key); @endphp
                <a class="staff-link is-child @if(request()->routeIs('admin.'.$definition['uri'].'.*')) is-active @endif" href="{{ route('admin.'.$definition['uri'].'.index') }}" @if(request()->routeIs('admin.'.$definition['uri'].'.*')) aria-current="page" @endif>{{ $definition['nav'] }}</a>
            @endforeach
        @endif
    @endforeach

    <a class="staff-link" href="{{ route('home') }}">የህዝብ በር</a>
</nav>
