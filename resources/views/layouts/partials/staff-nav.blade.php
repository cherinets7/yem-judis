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
            @if (in_array('weekly-reports', $section['keys'], true))
                @foreach (\App\Support\ProsecutorWeeklyMatrix::types() as $type => $label)
                    <a class="staff-link is-child @if(request()->routeIs('admin.prosecutor-weekly.*') && request('type') === $type) is-active @endif" href="{{ route('admin.prosecutor-weekly.index', ['type' => $type]) }}" @if(request()->routeIs('admin.prosecutor-weekly.*') && request('type') === $type) aria-current="page" @endif>{{ \App\Enums\ProsecutorReportType::from($type)->label() }} ሳምንታዊ</a>
                @endforeach
            @endif
        @endif
    @endforeach

    @if (\App\Support\Roles::allows($user, 'structure') || \App\Support\Roles::allows($user, 'experts') || \App\Support\Roles::allows($user, 'performance'))
        <div class="staff-heading">መዋቅር</div>
        @if (\App\Support\Roles::allows($user, 'structure'))
            <a class="staff-link @if(request()->routeIs('admin.offices.*')) is-active @endif" href="{{ route('admin.offices.index') }}" @if(request()->routeIs('admin.offices.*')) aria-current="page" @endif>ዞን እና ወረዳ</a>
        @endif
        @if (\App\Support\Roles::allows($user, 'experts'))
            <a class="staff-link @if(request()->routeIs('admin.experts.*')) is-active @endif" href="{{ route('admin.experts.index') }}" @if(request()->routeIs('admin.experts.*')) aria-current="page" @endif>ባለሙያዎች</a>
        @endif
        @if (\App\Support\Roles::allows($user, 'performance'))
            <a class="staff-link @if(request()->routeIs('admin.performance.*')) is-active @endif" href="{{ route('admin.performance.index') }}" @if(request()->routeIs('admin.performance.*')) aria-current="page" @endif>አፈጻጸም</a>
        @endif
    @endif

    <div class="staff-heading">ሥርዓት</div>
    <a class="staff-link @if(request()->routeIs('settings.*')) is-active @endif" href="{{ route('settings.edit') }}" @if(request()->routeIs('settings.*')) aria-current="page" @endif>ቅንብሮች</a>
    @if (\App\Support\Roles::allows($user, 'permissions'))
        <a class="staff-link @if(request()->routeIs('permissions.*')) is-active @endif" href="{{ route('permissions.edit') }}" @if(request()->routeIs('permissions.*')) aria-current="page" @endif>ፈቃዶች</a>
    @endif
    <a class="staff-link" href="{{ route('home') }}">የህዝብ በር</a>
</nav>
