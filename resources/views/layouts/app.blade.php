<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ዳሽቦርድ') — የየም ዞን ፍትህ መምሪያ</title>
    @include('layouts.partials.fonts')
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
            --sidebar: 18.5rem;
        }

        body {
            font-family: "Noto Sans Ethiopic", "Nyala", sans-serif;
            background:
                radial-gradient(720px 280px at 100% -10%, rgba(182, 137, 58, .14), transparent 55%),
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

        .staff-shell { min-height: 100vh; }

        .staff-sidebar {
            width: var(--sidebar);
            background: linear-gradient(180deg, var(--green-deep), var(--green) 42%, #145c43);
            color: #f7f3ea;
        }

        .staff-brand {
            display: flex;
            gap: .75rem;
            align-items: center;
            padding: 1.1rem 1rem 1rem;
            border-bottom: 1px solid rgba(243, 230, 200, .25);
            color: inherit;
            text-decoration: none;
        }
        .staff-brand:hover { color: #fff; }

        .brand-mark {
            width: 2.7rem;
            height: 2.7rem;
            border: 1.5px solid var(--gold);
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: var(--gold-soft);
            flex: 0 0 auto;
            font-weight: 700;
        }

        .brand-kicker { color: var(--gold-soft); font-size: .8rem; margin: 0; }
        .brand-title { font-weight: 700; font-size: 1rem; line-height: 1.35; margin: 0; }

        .staff-nav { display: flex; flex-direction: column; padding: .75rem; gap: .15rem; }
        .staff-heading {
            margin: .85rem .35rem .2rem;
            color: var(--gold-soft);
            font-size: .78rem;
            font-weight: 700;
        }
        .staff-link {
            color: #f7f3ea;
            text-decoration: none;
            border-radius: .55rem;
            padding: .45rem .7rem;
            line-height: 1.35;
        }
        .staff-link.is-child { padding-inline-start: 1.15rem; font-size: .95rem; }
        .staff-link:hover { background: rgba(255, 253, 248, .08); color: #fff; }
        .staff-link.is-active { background: var(--gold-soft); color: var(--green-deep); font-weight: 700; }

        .staff-top {
            background: var(--card);
            border-bottom: 1px solid var(--line);
        }
        .date-pill, .user-pill {
            background: var(--gold-soft);
            color: var(--green-deep);
            border-radius: 999px;
            padding: .25rem .75rem;
            font-size: .85rem;
        }
        .user-pill { background: #e7f0ea; }

        .staff-main { min-width: 0; }
        .page-card, .kpi-card, .staff-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 1rem;
            box-shadow: 0 10px 24px rgba(20, 36, 28, .04);
        }
        .kpi-card { border-top: 4px solid var(--gold); padding: 1rem 1.1rem; height: 100%; }
        .kpi-value { font-size: 2rem; font-weight: 700; line-height: 1.1; color: var(--green); }
        .kpi-label { color: var(--muted); margin-bottom: .35rem; }
        .meta { color: var(--muted); font-size: .85rem; }
        .table { --bs-table-bg: transparent; }
        .table thead th { color: var(--green); font-weight: 700; white-space: nowrap; }
        .empty-state { padding: 2rem 1rem; text-align: center; color: var(--muted); }
        .btn-success { background: var(--leaf); border-color: var(--leaf); }
        .btn-success:hover { background: var(--green); border-color: var(--green); }
        .form-label { font-weight: 600; }
        .form-control, .form-select { border-color: var(--line); }
        .form-control:focus, .form-select:focus {
            border-color: var(--leaf);
            box-shadow: 0 0 0 .2rem rgba(31, 107, 74, .15);
        }
        .alert { border: 0; border-radius: .8rem; }
        .offcanvas.staff-sidebar { width: min(20rem, 88vw); }

        .site-footer {
            border-top: 1px solid var(--line);
            color: var(--muted);
            background: var(--card);
        }

        .site-footer strong { color: var(--ink); }

        .site-footer-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem 2rem;
        }
    </style>
    @stack('head')
</head>
<body>
    <a class="skip-link" href="#content">ወደ ይዘት</a>
    <div class="staff-shell d-flex">
        <aside class="staff-sidebar d-none d-lg-flex flex-column">
            <a class="staff-brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">የ</span>
                <span>
                    <p class="brand-kicker">የሰራተኛ መዝገብ</p>
                    <p class="brand-title">የየም ዞን ፍትህ መምሪያ</p>
                </span>
            </a>
            <div class="overflow-auto">
                @include('layouts.partials.staff-nav')
            </div>
        </aside>

        <div class="staff-main flex-grow-1 d-flex flex-column">
            <header class="staff-top">
                <div class="d-flex flex-wrap align-items-center gap-2 px-3 py-2">
                    <button class="btn btn-success d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#staffMenu" aria-controls="staffMenu">ዝርዝር</button>
                    <div class="me-auto">
                        <div class="fw-bold">@yield('title', 'ዳሽቦርድ')</div>
                        <div class="date-pill d-inline-block mt-1">{{ \App\Helpers\EthiopianDateHelper::now() }} · የበጀት ዓመት {{ \App\Helpers\EthiopianDateHelper::currentBudgetYear() }}</div>
                    </div>
                    <a class="btn btn-outline-success btn-sm" id="staffNotifyLink" href="{{ route('notifications.index') }}">መልዕክት @if (auth()->user()->unreadNotifications()->count()) (<span id="staffNotifyCount">{{ auth()->user()->unreadNotifications()->count() }}</span>) @endif</a>
                    <a class="btn btn-outline-success btn-sm" href="{{ route('password.edit') }}">የይለፍ ቃል</a>
                    @if (\App\Support\TwoFactor::requiredFor(auth()->user()))
                        <a class="btn btn-outline-success btn-sm" href="{{ route('two-factor.edit') }}">2FA</a>
                    @endif
                    <div class="user-pill">{{ auth()->user()->displayName() }} · {{ auth()->user()->role->label() }}</div>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-success btn-sm" type="submit">ውጣ</button>
                    </form>
                </div>
            </header>

            <main id="content" class="px-3 px-lg-4 py-4 flex-grow-1">
                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif
                @yield('content')
            </main>

            <footer class="site-footer mt-auto">
                <div class="px-3 px-lg-4 py-3">
                    @include('layouts.partials.site-footer')
                </div>
            </footer>
        </div>
    </div>

    <div class="offcanvas offcanvas-start staff-sidebar d-lg-none" tabindex="-1" id="staffMenu" aria-labelledby="staffMenuLabel">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title h5 mb-0" id="staffMenuLabel">ዝርዝር</h2>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="ዝጋ"></button>
        </div>
        <div class="offcanvas-body p-0">
            @include('layouts.partials.staff-nav')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const link = document.getElementById('staffNotifyLink');
            if (!link) return;
            const poll = () => {
                fetch(@json(route('notifications.poll')), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                }).then((response) => response.ok ? response.json() : null).then((data) => {
                    if (!data) return;
                    const count = Number(data.unread || 0);
                    let label = 'መልዕክት';
                    if (count > 0) {
                        label += ' (<span id="staffNotifyCount">' + count + '</span>)';
                    }
                    link.innerHTML = label;
                }).catch(() => {});
            };
            setInterval(poll, 30000);
        })();
    </script>
    @stack('scripts')
</body>
</html>
