<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Sistem Absensi NFC')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary: #0a0e1a;
            --bg-secondary: #111827;
            --bg-card: rgba(17, 24, 39, 0.7);
            --bg-glass: rgba(255, 255, 255, 0.04);
            --border-glass: rgba(255, 255, 255, 0.08);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-blue: #3b82f6;
            --accent-cyan: #06b6d4;
            --accent-emerald: #10b981;
            --accent-amber: #f59e0b;
            --accent-rose: #f43f5e;
            --accent-violet: #8b5cf6;
            --gradient-primary: linear-gradient(135deg, #3b82f6, #8b5cf6);
            --gradient-success: linear-gradient(135deg, #10b981, #06b6d4);
            --gradient-warning: linear-gradient(135deg, #f59e0b, #f97316);
            --gradient-danger: linear-gradient(135deg, #f43f5e, #e11d48);
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.5);
            --shadow-glow-blue: 0 0 30px rgba(59, 130, 246, 0.3);
            --shadow-glow-emerald: 0 0 30px rgba(16, 185, 129, 0.3);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 20% 50%, rgba(59, 130, 246, 0.08), transparent 50%),
                        radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.06), transparent 50%),
                        radial-gradient(ellipse at 50% 80%, rgba(6, 182, 212, 0.05), transparent 50%);
            z-index: -1;
            animation: bgShift 20s ease-in-out infinite alternate;
        }

        @keyframes bgShift {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-5%, 5%) rotate(3deg); }
        }

        /* ── Navigation ─────────────────── */
        .nav-bar {
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(20px) saturate(1.5);
            -webkit-backdrop-filter: blur(20px) saturate(1.5);
            background: rgba(10, 14, 26, 0.85);
            border-bottom: 1px solid var(--border-glass);
            padding: 0 16px;
        }

        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .nav-brand .brand-icon {
            width: 36px;
            height: 36px;
            background: var(--gradient-primary);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .nav-links {
            display: flex;
            gap: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--text-primary);
            background: var(--bg-glass);
        }

        .nav-link.active {
            background: rgba(59, 130, 246, 0.15);
            color: var(--accent-blue);
        }

        /* ── Container ─────────────────── */
        .page-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px 16px 100px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* ── Cards ─────────────────────── */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius-lg);
            padding: 24px;
            margin-bottom: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .glass-card:hover {
            box-shadow: var(--shadow-md);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title .icon {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .card-title .icon.blue { background: rgba(59, 130, 246, 0.15); }
        .card-title .icon.emerald { background: rgba(16, 185, 129, 0.15); }
        .card-title .icon.violet { background: rgba(139, 92, 246, 0.15); }

        /* ── Form Elements ─────────────── */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-select,
        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s ease;
            appearance: none;
            -webkit-appearance: none;
        }

        .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
        }

        .form-select option {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .form-select:focus,
        .form-input:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .form-input[readonly] {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* ── Buttons ───────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
            outline: none;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(rgba(255,255,255,0.1), transparent);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn:hover::after { opacity: 1; }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow-blue);
        }

        .btn-success {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow-emerald);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-glass);
            color: var(--text-secondary);
        }

        .btn-outline:hover {
            background: var(--bg-glass);
            color: var(--text-primary);
        }

        .btn-block {
            width: 100%;
        }

        .btn-lg {
            padding: 16px 32px;
            font-size: 1.05rem;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ── Status Messages ───────────── */
        .status-box {
            padding: 14px 18px;
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            font-weight: 500;
            display: none;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        .status-box.show { display: flex; }

        .status-box.info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }

        .status-box.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
        }

        .status-box.error {
            background: rgba(244, 63, 94, 0.1);
            border: 1px solid rgba(244, 63, 94, 0.2);
            color: #fda4af;
        }

        .status-box.warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: #fcd34d;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Result Card ───────────────── */
        .result-card {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(6, 182, 212, 0.05));
            border: 1px solid rgba(16, 185, 129, 0.15);
            border-radius: var(--radius-lg);
            padding: 24px;
            display: none;
            animation: scaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .result-card.show { display: block; }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .result-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .result-avatar {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
        }

        .result-avatar.hadir { background: var(--gradient-success); }
        .result-avatar.terlambat { background: var(--gradient-warning); }

        .result-name {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .result-nim {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .result-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .result-item {
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-sm);
            padding: 12px;
        }

        .result-item-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .result-item-value {
            font-size: 0.95rem;
            font-weight: 600;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .badge-hadir {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
        }

        .badge-terlambat {
            background: rgba(245, 158, 11, 0.15);
            color: #fcd34d;
        }

        /* ── NFC Pulse Animation ────────── */
        .nfc-visual {
            text-align: center;
            padding: 32px 0;
        }

        .nfc-ring {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nfc-ring .ring {
            position: absolute;
            border: 2px solid rgba(59, 130, 246, 0.3);
            border-radius: 50%;
            animation: nfcPulse 2s ease-out infinite;
        }

        .nfc-ring .ring:nth-child(1) { width: 60px; height: 60px; animation-delay: 0s; }
        .nfc-ring .ring:nth-child(2) { width: 85px; height: 85px; animation-delay: 0.4s; }
        .nfc-ring .ring:nth-child(3) { width: 110px; height: 110px; animation-delay: 0.8s; }

        .nfc-ring .nfc-icon {
            font-size: 2rem;
            z-index: 1;
        }

        .nfc-ring.scanning .ring {
            border-color: rgba(16, 185, 129, 0.4);
        }

        @keyframes nfcPulse {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        .nfc-text {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        /* ── Table ─────────────────────── */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-glass);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .data-table th {
            background: rgba(255, 255, 255, 0.04);
            padding: 12px 14px;
            text-align: left;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            white-space: nowrap;
            border-bottom: 1px solid var(--border-glass);
        }

        .data-table td {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            white-space: nowrap;
        }

        .data-table tbody tr {
            transition: background 0.15s ease;
        }

        .data-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── Filter bar ────────────────── */
        .filter-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-bar .form-select,
        .filter-bar .form-input {
            flex: 1;
            min-width: 140px;
            padding: 10px 14px;
            font-size: 0.85rem;
        }

        .filter-bar .btn {
            padding: 10px 20px;
            font-size: 0.85rem;
        }

        /* ── Summary Cards ─────────────── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        .summary-card {
            background: var(--bg-glass);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius-md);
            padding: 16px;
            text-align: center;
        }

        .summary-card .count {
            font-size: 2rem;
            font-weight: 800;
            background: var(--gradient-success);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .summary-card .label {
            font-size: 0.78rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* ── Spinner ───────────────────── */
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Mobile Menu Toggle ─────────── */
        .nav-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.3rem;
            cursor: pointer;
            padding: 6px;
        }

        /* ── Empty State ───────────────── */
        .empty-state {
            text-align: center;
            padding: 48px 16px;
            color: var(--text-muted);
        }

        .empty-state .icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        @media (max-width: 640px) {
            .nav-toggle { display: block; }
            .nav-links {
                display: none;
                position: absolute;
                top: 60px;
                left: 0;
                right: 0;
                background: rgba(10, 14, 26, 0.97);
                backdrop-filter: blur(20px);
                padding: 8px 16px;
                flex-direction: column;
                border-bottom: 1px solid var(--border-glass);
            }
            .nav-links.open { display: flex; }
            .nav-link { padding: 12px 16px; }
            .result-details { grid-template-columns: 1fr; }
            .page-header h1 { font-size: 1.4rem; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="nav-bar">
        <div class="nav-inner">
            <a href="{{ route('absensi.index') }}" class="nav-brand">
                <span class="brand-icon">📡</span>
                <span>NFC Absensi</span>
            </a>
            <button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')" aria-label="Toggle menu">☰</button>
            <div class="nav-links">
                <a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.index') ? 'active' : '' }}">
                    📲 Scan Absensi
                </a>
                <a href="{{ route('absensi.daftarKartu') }}" class="nav-link {{ request()->routeIs('absensi.daftarKartu') ? 'active' : '' }}">
                    💳 Daftar Kartu
                </a>
                <a href="{{ route('absensi.riwayat') }}" class="nav-link {{ request()->routeIs('absensi.riwayat') ? 'active' : '' }}">
                    📋 Riwayat
                </a>
            </div>
        </div>
    </nav>

    <div class="page-container">
        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
