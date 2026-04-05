<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <style>
        :root {
            --bg: #f4efe6;
            --panel: #fffaf2;
            --ink: #1f2933;
            --muted: #52606d;
            --line: #d9cbb5;
            --accent: #0f766e;
            --warning: #b45309;
            --danger: #b91c1c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            background: radial-gradient(circle at top left, #fff7ed, var(--bg) 55%);
            color: var(--ink);
        }
        a { color: inherit; }
        .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 10px 30px rgba(31, 41, 51, 0.06);
        }
        .stack { display: grid; gap: 16px; }
        .hero {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: start;
            margin-bottom: 20px;
        }
        .hero-copy { max-width: 760px; }
        .eyebrow { color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; font-size: 12px; }
        h1, h2, h3, p { margin-top: 0; }
        .nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: space-between;
            align-items: center;
        }
        .nav-links { display: flex; flex-wrap: wrap; gap: 10px; }
        .nav-meta { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .nav a, .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.5);
            padding: 10px 14px;
            text-decoration: none;
            cursor: pointer;
        }
        .button-primary {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        .button-warning {
            background: var(--warning);
            color: white;
            border-color: var(--warning);
        }
        .button-danger {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
        }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            text-align: left;
            padding: 10px 0;
            border-bottom: 1px solid rgba(217, 203, 181, 0.8);
            vertical-align: top;
        }
        th { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; }
        .pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.1);
            color: var(--accent);
            font-size: 12px;
        }
        .warn { background: rgba(180, 83, 9, 0.12); color: var(--warning); }
        .danger { background: rgba(185, 28, 28, 0.12); color: var(--danger); }
        .success { background: rgba(15, 118, 110, 0.12); color: var(--accent); }
        .muted { color: var(--muted); }
        .flash {
            margin-bottom: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(15, 118, 110, 0.08);
            border: 1px solid rgba(15, 118, 110, 0.2);
        }
        .errors {
            margin-bottom: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(185, 28, 28, 0.08);
            border: 1px solid rgba(185, 28, 28, 0.2);
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }
        label {
            display: grid;
            gap: 8px;
            font-size: 14px;
        }
        input, select, textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            font: inherit;
            background: #fffdf9;
        }
        textarea { min-height: 110px; resize: vertical; }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }
        .section-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 16px;
        }
        .list-reset {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .list-reset li {
            padding: 12px 0;
            border-bottom: 1px solid rgba(217, 203, 181, 0.8);
        }
        @media (max-width: 900px) {
            .hero, .grid-2, .grid-3, .section-grid { grid-template-columns: 1fr; display: grid; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <nav class="nav">
            <div class="nav-links">
                <a href="{{ route('dashboard.operational') }}">Dashboard</a>
                <a href="{{ route('digital-transactions.index') }}">Transaksi Digital</a>
                <a href="{{ route('digital-transactions.queue') }}">Queue Pending</a>
                <a href="{{ route('pos.index') }}">POS Barang</a>
                <a href="{{ route('cash-sessions.index') }}">Sesi Kas</a>
                <a href="{{ route('digital-transactions.create') }}">Buat Tiket Baru</a>
            </div>
            <div class="nav-meta">
                @auth
                    <span class="pill">{{ auth()->user()->name }} - {{ auth()->user()->role->label() }}</span>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="button" type="submit">Logout</button>
                    </form>
                @endauth
            </div>
        </nav>

        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <strong>Ada input yang perlu diperbaiki.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
