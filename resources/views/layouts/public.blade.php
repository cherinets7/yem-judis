<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'መነሻ') — የየም ዞን ፍትህ መምሪያ</title>
    @include('layouts.partials.fonts')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #14241c;
            --green: #0f3d2e;
            --green-deep: #08261c;
            --leaf: #1c7a52;
            --gold: #c8963e;
            --gold-soft: #f6ead0;
            --paper: #f4f1ea;
            --card: #fffcf7;
            --line: #e6dfd2;
            --muted: #5d675f;
        }

        body {
            font-family: "Noto Sans Ethiopic", "Nyala", sans-serif;
            background:
                radial-gradient(880px 420px at 100% -8%, rgba(200, 150, 62, .16), transparent 55%),
                radial-gradient(640px 360px at -8% 18%, rgba(28, 122, 82, .08), transparent 50%),
                var(--paper);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.65;
        }

        main { flex: 1 0 auto; }

        a { color: var(--leaf); }
        a:hover { color: var(--green); }

        .skip-link {
            position: absolute;
            left: 1rem;
            top: -3rem;
            background: var(--gold);
            color: var(--ink);
            padding: .4rem .8rem;
            z-index: 1080;
        }
        .skip-link:focus { top: .5rem; }

        .portal-header {
            position: sticky;
            top: 0;
            z-index: 30;
            background: linear-gradient(180deg, #071910 0%, #0e3328 100%);
            color: #f7f3ea;
            box-shadow: 0 12px 32px rgba(7, 25, 16, .18);
        }

        .header-bar { padding: 1rem 0 .35rem; }

        .brand-mark {
            width: 3.1rem;
            height: 3.1rem;
            border: 1.5px solid var(--gold);
            border-radius: 1rem;
            display: grid;
            place-items: center;
            color: var(--gold-soft);
            flex: 0 0 auto;
            background: rgba(255, 252, 247, .06);
        }

        .brand-kicker {
            letter-spacing: .04em;
            color: var(--gold-soft);
            font-size: .85rem;
            margin: 0;
        }

        .brand-title {
            font-weight: 700;
            font-size: 1.35rem;
            line-height: 1.35;
            margin: 0;
        }

        .date-badge {
            background: rgba(255, 252, 247, .08);
            border: 1px solid rgba(246, 234, 208, .28);
            border-radius: 1rem;
            padding: .65rem 1rem;
            min-width: 14rem;
            backdrop-filter: blur(8px);
        }

        .date-badge .kicker {
            display: block;
            color: var(--gold-soft);
            font-size: .78rem;
        }

        .date-badge strong {
            display: block;
            font-weight: 600;
            line-height: 1.45;
        }

        .portal-nav {
            background: rgba(0, 0, 0, .16);
        }

        .portal-nav .nav-link {
            color: #f7f3ea;
            border-radius: 999px;
            padding: .42rem .95rem;
            font-weight: 500;
        }

        .portal-nav .nav-link.active,
        .portal-nav .nav-link:hover {
            background: var(--gold);
            color: var(--green-deep);
        }

        .navbar-toggler {
            border-color: rgba(243, 230, 200, .5);
        }

        .navbar-toggler-icon {
            filter: invert(1);
        }

        main { padding: 2.25rem 0 3.5rem; }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(16rem, .8fr);
            gap: 1.75rem;
            align-items: center;
            background:
                radial-gradient(420px 220px at 88% 0%, rgba(200, 150, 62, .28), transparent 60%),
                linear-gradient(145deg, #0b2a20 0%, #146046 72%, #0f3d2e 100%);
            color: #f7f3ea;
            border: 0;
            border-radius: 1.75rem;
            padding: clamp(1.4rem, 3vw, 2.4rem);
            box-shadow: 0 28px 60px rgba(8, 38, 28, .18);
        }

        .hero h1 {
            font-weight: 700;
            font-size: clamp(1.9rem, 4vw, 3rem);
            line-height: 1.28;
            letter-spacing: -.02em;
            margin: 0;
        }

        .hero-kicker {
            color: var(--gold-soft);
            font-weight: 600;
            margin: 0;
        }

        .hero-lead {
            max-width: 38rem;
            margin: 1rem 0 0;
            color: rgba(247, 243, 234, .88);
            font-size: 1.05rem;
        }

        .hero .btn {
            border-radius: 999px;
            padding: .62rem 1.15rem;
            font-weight: 700;
        }

        .hero .btn-outline-light {
            border-color: rgba(247, 243, 234, .45);
            color: #f7f3ea;
        }

        .hero .btn-outline-light:hover {
            background: #fffcf7;
            color: var(--green-deep);
        }

        .hero-services {
            display: grid;
            gap: .7rem;
        }

        .hero-services a {
            display: grid;
            grid-template-columns: auto 1fr;
            grid-template-rows: auto auto;
            column-gap: .8rem;
            text-decoration: none;
            color: #f7f3ea;
            background: rgba(255, 252, 247, .08);
            border: 1px solid rgba(246, 234, 208, .18);
            border-radius: 1rem;
            padding: .8rem .9rem;
        }

        .hero-services a:hover {
            background: rgba(255, 252, 247, .16);
            color: #fff;
        }

        .hero-services span {
            grid-row: 1 / span 2;
            align-self: center;
            color: var(--gold);
            font-weight: 700;
        }

        .hero-services strong { font-size: 1rem; }
        .hero-services small { color: rgba(247, 243, 234, .72); }

        @media (max-width: 991.98px) {
            .hero { grid-template-columns: 1fr; }
            .date-badge { min-width: 0; }
        }

        .section-title {
            font-weight: 700;
            font-size: 1.35rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .55rem;
        }

        .section-title::before {
            content: "";
            width: .55rem;
            height: .55rem;
            border-radius: 50%;
            background: var(--gold);
            flex: 0 0 auto;
        }

        h1.section-title { font-size: clamp(1.6rem, 3vw, 2.15rem); }

        .form-label { font-weight: 600; }

        .form-control,
        .form-select {
            border-color: var(--line);
            border-radius: .8rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--leaf);
            box-shadow: 0 0 0 .2rem rgba(28, 122, 82, .16);
        }

        .portal-card {
            background: var(--card);
            border: 1px solid rgba(230, 223, 210, .9);
            border-radius: 1.15rem;
            height: 100%;
            padding: 1.15rem 1.2rem;
            box-shadow: 0 12px 28px rgba(20, 36, 28, .04);
        }

        .portal-card:hover {
            box-shadow: 0 18px 36px rgba(20, 36, 28, .08);
        }

        .portal-card h2,
        .portal-card h3 {
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.45;
        }

        .meta {
            color: var(--muted);
            font-size: .86rem;
        }

        .badge-gold {
            background: var(--gold-soft);
            color: #6d4d12;
            font-weight: 600;
        }

        .badge-green {
            background: #e5f3ec;
            color: var(--green);
            font-weight: 600;
        }

        .btn-gold,
        .btn-green {
            border-radius: 999px;
        }

        .btn-gold {
            background: var(--gold);
            border-color: var(--gold);
            color: #1d1608;
            font-weight: 700;
        }

        .btn-gold:hover {
            background: #a37830;
            border-color: #a37830;
            color: #1d1608;
        }

        .btn-green {
            background: var(--green);
            border-color: var(--green);
            color: #f7f3ea;
            font-weight: 700;
        }

        .btn-green:hover {
            background: var(--green-deep);
            color: #fff;
        }

        .filter-bar,
        .table-wrap,
        .post-list {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 1.15rem;
            padding: 1.1rem;
            box-shadow: 0 12px 28px rgba(20, 36, 28, .04);
        }

        .table {
            --bs-table-bg: transparent;
            margin-bottom: 0;
        }

        .table thead th {
            color: var(--muted);
            font-weight: 600;
            white-space: nowrap;
        }

        .portal-tabs .nav-link {
            color: var(--green);
            font-weight: 600;
        }

        .portal-tabs .nav-link.active {
            color: var(--green-deep);
            border-color: var(--line) var(--line) var(--card);
            background: var(--card);
        }

        .empty-state {
            border: 1px dashed var(--line);
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            color: var(--muted);
            background: rgba(255, 253, 248, .7);
        }

        .post-body { white-space: pre-wrap; }

        .site-footer {
            border-top: 0;
            color: rgba(247, 243, 234, .78);
            background: #0c2e23;
            margin-top: auto;
        }

        .site-footer strong { color: var(--gold-soft); }

        .site-footer-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem 2rem;
            padding: 1.8rem 0 2.1rem;
        }

        .page-link {
            color: var(--green);
        }

        .page-item.active .page-link,
        .page-link:hover {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
        }
    </style>
</head>
<body>
    <a class="skip-link" href="#content">ወደ ይዘት ይሂዱ</a>
    <header class="portal-header">
        <div class="container header-bar">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <a class="d-flex align-items-center gap-3 text-decoration-none text-reset" href="{{ route('home') }}">
                    <span class="brand-mark" aria-hidden="true">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                            <path d="M12 3v3M8 21h8M9 21c0-3 1.2-4.5 3-6 1.8 1.5 3 3 3 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            <path d="M4 10h16M6 10l2 4h8l2-4" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span>
                        <p class="brand-kicker">የድጅታላይዜሽን ሥርዓት</p>
                        <p class="brand-title">የየም ዞን ፍትህ መምሪያ</p>
                    </span>
                </a>
                <div class="date-badge">
                    <span class="kicker">ዛሬ</span>
                    <strong>{{ \App\Helpers\EthiopianDateHelper::now() }}</strong>
                    <span class="kicker">የበጀት ዓመት {{ \App\Helpers\EthiopianDateHelper::currentBudgetYear() }}</span>
                </div>
            </div>
        </div>
        <nav class="portal-nav navbar navbar-expand-lg" aria-label="ዋና ዳሰሳ">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav" aria-controls="portalNav" aria-expanded="false" aria-label="ምናሌ">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="portalNav">
                    <ul class="navbar-nav gap-lg-1 py-2">
                        <li class="nav-item">
                            <a class="nav-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}">መነሻ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request()->routeIs('cases.tracking')) active @endif" href="{{ route('cases.tracking') }}">የጉዳዮች አስተዳደር</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request()->routeIs('laws.*')) active @endif" href="{{ route('laws.index') }}">ህጎች</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request()->routeIs('community.*')) active @endif" href="{{ route('community.index') }}">ሌሎች</a>
                        </li>
                        <li class="nav-item">
                            @auth
                                <a class="nav-link @if(request()->routeIs('dashboard')) active @endif" href="{{ route('dashboard') }}">የሰራተኛ ገጽ</a>
                            @else
                                <a class="nav-link @if(request()->routeIs('login')) active @endif" href="{{ route('login') }}">የሰራተኛ መግቢያ</a>
                            @endauth
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main id="content">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            @include('layouts.partials.site-footer')
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
