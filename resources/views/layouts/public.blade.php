<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'መነሻ') — የየም ዞን ፍትህ መምሪያ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #17241c;
            --green: #0f3d2e;
            --green-deep: #0a2b20;
            --leaf: #1f6b4a;
            --gold: #b6893a;
            --gold-soft: #f3e6c8;
            --paper: #f3efe4;
            --card: #fffdf8;
            --line: #e3d9c4;
            --muted: #5e6a62;
        }

        body {
            font-family: "Noto Sans Ethiopic", "Nyala", sans-serif;
            background:
                radial-gradient(circle at top right, rgba(182, 137, 58, 0.16), transparent 28%),
                var(--paper);
            color: var(--ink);
            min-height: 100vh;
        }

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
            background: linear-gradient(160deg, var(--green-deep), var(--green) 55%, #145c43);
            color: #f7f3ea;
        }

        .brand-mark {
            width: 3rem;
            height: 3rem;
            border: 1.5px solid var(--gold);
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: var(--gold-soft);
            flex: 0 0 auto;
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
            background: rgba(255, 253, 248, .08);
            border: 1px solid rgba(243, 230, 200, .35);
            border-radius: 1rem;
            padding: .7rem .95rem;
            min-width: 14rem;
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
            padding: .45rem .9rem;
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

        main { padding: 2rem 0 3rem; }

        .hero {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 1.25rem;
            padding: 1.6rem;
            box-shadow: 0 16px 40px rgba(15, 61, 46, .06);
        }

        .hero h1 {
            font-weight: 700;
            font-size: clamp(1.7rem, 3vw, 2.5rem);
            line-height: 1.35;
        }

        .section-title {
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .portal-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 1rem;
            height: 100%;
            padding: 1.1rem 1.15rem;
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
            border-radius: 1rem;
            padding: 1rem;
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

        .portal-footer {
            border-top: 1px solid var(--line);
            color: var(--muted);
            padding: 1.4rem 0 2rem;
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
        <div class="container py-3">
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

    <footer class="portal-footer">
        <div class="container d-flex flex-wrap justify-content-between gap-3">
            <div>
                <strong class="text-dark">የየም ዞን ፍትህ መምሪያ</strong>
                <div>ሳጃ፣ የየም ዞን፣ ማዕከላዊ ኢትዮጵያ</div>
            </div>
            <div>የህዝብ መረጃ በር ብቻ። የጉዳይ ዝርዝር የሚታየው ከታተመ በኋላ ነው።</div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
