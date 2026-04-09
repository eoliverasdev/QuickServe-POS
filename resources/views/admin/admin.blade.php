<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <title>Admin - La Cresta</title>
    <script>
        // Capturem el hash ABANS que el navegador faci scroll automàtic, i el netegem
        window.__initialHash = window.location.hash.substring(1);
        if (window.__initialHash) {
            history.replaceState(null, '', window.location.pathname + window.location.search);
        }
    </script>
    <style>
        /* Unificació de colors i fonts amb el TPV */
        :root {
            --primary: #ffed05;
            --primary-dark: #d4c200;
            --danger: #ef4444;
            --success: #28a745;
            --dark: #333;
            --bg: #f8f9fa;
            --sidebar-bg: #ffffff;
        }



        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--bg);
            margin: 0;
            display: flex;
            color: var(--dark);
        }

        button,
        .nav-link,
        .btn {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        /* Sidebar Estil La Cresta */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            padding: 25px 20px;
            box-sizing: border-box;
            z-index: 100;
            border-right: 1px solid #eee;
            display: flex;
            flex-direction: column;
        }

        .sidebar-links {
            flex-grow: 1;
            overflow-y: auto;
            min-height: 0;
            padding-right: 5px;
            margin-bottom: 10px;
        }

        /* Scrollbar per al menú lateral */
        .sidebar-links::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-links::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-links::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .sidebar-links::-webkit-scrollbar-thumb:hover {
            background: #cbd5e0;
        }

        .sidebar-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 35px;
        }

        .sidebar-logo img {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }

        .sidebar-logo div {
            height: 2px;
            width: 40px;
            background: var(--primary);
            border-radius: 2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #666;
            text-decoration: none;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: 0.2s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
        }

        .nav-link:hover {
            background: #fafafa;
            color: #000;
        }

        .nav-link.active {
            background: rgba(255, 237, 5, 0.15);
            color: #000;
            font-weight: 700;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 40px;
            width: calc(100% - 280px);
        }

        h1 {
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -0.5px;
            margin-bottom: 30px;
        }

        .section {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Alertas */
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 12px;
            font-weight: bold;
            border-left: 5px solid;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-color: #22c55e;
        }

        /* Stats & Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            text-align: left;
            border: 1px solid #eee;
            transition: 0.3s;
        }

        .stat-card h3 {
            margin: 0;
            color: #999;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card p {
            margin: 10px 0 0;
            font-size: 2rem;
            font-weight: 900;
            color: #333;
        }

        .stat-card small {
            color: #aaa;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .border-orange {
            border-top: 4px solid var(--primary);
        }

        .border-green {
            border-top: 4px solid #22c55e;
        }

        .border-blue {
            border-top: 4px solid #3b82f6;
        }

        .border-purple {
            border-top: 4px solid #8b5cf6;
        }

        /* Forms & Tables */
        .card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            margin-bottom: 30px;
            border: 1px solid #eee;
        }

        .search-bar {
            width: 100%;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 1rem;
            background: #fafafa;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        th {
            text-align: left;
            padding: 15px;
            color: #aaa;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        td {
            padding: 15px;
            background: white;
            border-top: 1px solid #f8f8f8;
            border-bottom: 1px solid #f8f8f8;
        }

        td:first-child {
            border-left: 1px solid #f8f8f8;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        td:last-child {
            border-right: 1px solid #f8f8f8;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 800;
            cursor: pointer;
            font-size: 0.8rem;
            transition: 0.2s;
            text-transform: uppercase;
        }

        .btn-add {
            background: var(--primary);
            color: #000;
        }

        .btn-edit {
            background: #f0f0f0;
            color: #666;
            margin-right: 5px;
        }

        .btn-delete {
            background: #fff0f0;
            color: var(--danger);
        }

        .btn-logout {
            background: #fdf2f2;
            color: #e04a4a;
            border: 1px solid #fee2e2;
            width: 100%;
            margin-top: 20px;
        }

        .admin-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            align-items: end;
        }

        .admin-form label {
            font-size: 0.75rem;
            font-weight: 800;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }

        .admin-form input,
        .admin-form select {
            padding: 12px;
            border: 1px solid #eee;
            border-radius: 10px;
            font-size: 0.95rem;
            background: #fafafa;
            font-family: inherit;
        }

        .edit-row {
            background: #fffbeb !important;
            display: none;
        }

        .edit-row.active {
            display: table-row;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            background: #f0f0f0;
            color: #666;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* ─── ESTADÍSTIQUES ─── */
        .analytics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .analytics-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .card-title {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #aaa;
            letter-spacing: 1px;
            margin: 0 0 20px 0;
        }

        /* Barra de progrés de fama */
        .fame-bar-wrap {
            margin-bottom: 12px;
        }

        .fame-bar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .fame-bar-track {
            background: #f4f4f8;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }

        .fame-bar-fill {
            height: 100%;
            border-radius: 10px;
            background: linear-gradient(90deg, #4e73df, #7b9ff5);
            transition: width 1s ease;
        }

        .fame-rank {
            font-size: 0.75rem;
            color: #aaa;
            font-weight: 700;
            margin-right: 5px;
        }

        .fame-rank-1 {
            color: #f59e0b;
        }

        .fame-rank-2 {
            color: #94a3b8;
        }

        .fame-rank-3 {
            color: #b87333;
        }

        /* Dia selector */
        .day-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .day-tab {
            padding: 8px 14px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.8rem;
            background: #f4f4f8;
            color: #666;
            transition: 0.2s;
            font-family: inherit;
        }

        .day-tab.active {
            background: #4e73df;
            color: #fff;
        }

        .day-tab.today {
            border: 2px solid #4e73df;
        }

        /*  heatmap */
        .hour-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 6px;
        }

        .hour-cell {
            aspect-ratio: 1;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 0.6rem;
            font-weight: 800;
            cursor: default;
            transition: 0.2s;
        }

        .hour-cell:hover {
            transform: scale(1.1);
        }

        .hour-cell span {
            font-size: 0.55rem;
            color: inherit;
            opacity: 0.8;
        }

        /* Pagament Split */
        .payment-split {
            display: flex;
            gap: 15px;
        }

        .payment-pill {
            flex: 1;
            background: #f8f9fe;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }

        .payment-pill .amount {
            font-size: 1.6rem;
            font-weight: 900;
        }

        .payment-pill .label {
            font-size: 0.75rem;
            color: #aaa;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* ─── HISTORIAL FILTRE & ACCORDION ─── */
        .filter-pills {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-pill {
            padding: 8px 18px;
            border-radius: 20px;
            border: 2px solid #eee;
            background: #fff;
            font-weight: 700;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            color: #666;
        }

        .filter-pill.active {
            border-color: #4e73df;
            background: #4e73df;
            color: #fff;
        }

        .filter-pill.pill-efectiu.active {
            border-color: #16a34a;
            background: #16a34a;
        }

        .filter-pill.pill-targeta.active {
            border-color: #3b82f6;
            background: #3b82f6;
        }

        /* Historial de vendes — optimització tàctil */
        .historial-filters {
            margin-bottom: 0;
        }

        .historial-filters-row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 16px 28px;
            margin-bottom: 20px;
        }

        .historial-payment-block {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1 1 260px;
            min-width: 0;
        }

        .historial-block-label {
            font-size: 0.72rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .historial-payment-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .historial-filters .filter-pill {
            min-height: 48px;
            padding: 12px 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            border-radius: 14px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .historial-date-compact-form {
            flex: 1 1 240px;
            max-width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 10px 14px;
            background: #f8fafc;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
        }

        .historial-date-chips-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .date-wheel-chip {
            flex: 1;
            min-width: 108px;
            min-height: 48px;
            padding: 10px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            font-size: 0.92rem;
            font-weight: 700;
            color: #1e293b;
            cursor: pointer;
            font-family: inherit;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
            transition: border-color 0.15s, background 0.15s;
        }

        .date-wheel-chip:active {
            background: #f1f5f9;
        }

        .date-chip-sep {
            color: #94a3b8;
            font-weight: 800;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .historial-date-actions-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        /* Selector de data estil roda (iOS-like) */
        .date-wheel-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            align-items: flex-end;
            justify-content: center;
        }

        .date-wheel-overlay.is-open {
            display: flex;
        }

        .date-wheel-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            touch-action: manipulation;
        }

        .date-wheel-sheet {
            position: relative;
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 20px 20px 0 0;
            padding: 12px 12px max(20px, env(safe-area-inset-bottom));
            box-shadow: 0 -12px 40px rgba(0, 0, 0, 0.18);
            animation: dateWheelSlide 0.28s ease-out;
        }

        @keyframes dateWheelSlide {
            from {
                transform: translateY(100%);
            }
            to {
                transform: translateY(0);
            }
        }

        .date-wheel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 4px 4px 12px;
            border-bottom: 1px solid #e9edf5;
            margin-bottom: 8px;
        }

        .date-wheel-title {
            font-weight: 800;
            font-size: 0.95rem;
            color: #334155;
            flex: 1;
            text-align: center;
        }

        .date-wheel-header-btn {
            min-height: 44px;
            min-width: 88px;
            padding: 8px 12px;
            border: none;
            border-radius: 10px;
            background: transparent;
            font-weight: 700;
            font-size: 0.9rem;
            color: #64748b;
            cursor: pointer;
            font-family: inherit;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .date-wheel-header-primary {
            color: #4e73df;
            font-weight: 800;
        }

        .date-wheel-columns {
            display: flex;
            gap: 4px;
            padding: 4px 0 8px;
        }

        .date-wheel-col {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .date-wheel-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .date-wheel-window {
            position: relative;
            width: 100%;
            height: 220px;
            border-radius: 12px;
            background: #f8fafc;
            overflow: hidden;
        }

        .date-wheel-highlight {
            position: absolute;
            left: 4px;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            height: 44px;
            border-top: 1px solid #4e73df;
            border-bottom: 1px solid #4e73df;
            background: rgba(78, 115, 223, 0.07);
            border-radius: 8px;
            pointer-events: none;
            z-index: 2;
        }

        .date-wheel-scroll {
            position: relative;
            z-index: 1;
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            scroll-snap-type: y mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .date-wheel-scroll::-webkit-scrollbar {
            display: none;
        }

        .wheel-pad {
            height: 88px;
            flex-shrink: 0;
        }

        .wheel-item {
            height: 44px;
            line-height: 44px;
            text-align: center;
            font-size: 1.05rem;
            font-weight: 600;
            color: #334155;
            scroll-snap-align: center;
            scroll-snap-stop: always;
            user-select: none;
        }

        @media (min-width: 480px) {
            .date-wheel-overlay.is-open {
                align-items: center;
            }

            .date-wheel-sheet {
                border-radius: 20px;
                margin: 16px;
                padding-bottom: 20px;
            }
        }

        .btn-touch-historial {
            min-height: 48px;
            min-width: 148px;
            padding: 14px 22px !important;
            font-size: 0.9rem !important;
            border-radius: 12px !important;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .pagination-admin {
            display: inline-flex;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .pagination-admin a,
        .pagination-admin span {
            min-height: 48px;
            min-width: 48px;
            padding: 12px 18px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem !important;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .venda-row {
            cursor: pointer;
            transition: background 0.15s;
        }

        .venda-row:hover td {
            background: #f8f9fe !important;
        }

        .venda-row.expanded td {
            background: #f0f4ff !important;
        }

        .venda-detail-row td {
            padding: 0 !important;
            background: #f8faff !important;
            border: none !important;
        }

        .venda-detail-inner {
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.35s ease, padding 0.35s ease;
            padding: 0 15px;
        }

        .venda-detail-inner.open {
            max-height: 500px;
            padding: 14px 20px;
        }

        .detail-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid #eaeaf5;
            font-size: 0.88rem;
        }

        .detail-item-row:last-child {
            border-bottom: none;
        }

        .detail-qty {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e8edff;
            color: #4e73df;
            font-weight: 900;
            font-size: 0.75rem;
            border-radius: 8px;
            min-width: 28px;
            height: 24px;
            padding: 0 6px;
            margin-right: 10px;
        }

        .detail-price {
            font-weight: 700;
            color: #16a34a;
        }

        .chevron-icon {
            display: inline-block;
            transition: transform 0.25s;
            font-size: 0.7rem;
            margin-left: 6px;
            color: #bbb;
        }

        .venda-row.expanded .chevron-icon {
            transform: rotate(180deg);
        }

        @media (max-width: 1400px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .analytics-grid-3 {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="{{ asset('images/logo-la-cresta-sense-fons.png') }}" alt="Logo La Cresta">
            <div></div>
        </div>

        <div class="sidebar-links">
            <button class="nav-link active" onclick="showSection('resum', this)">📊 Resum</button>

            <div
                style="margin-top: 15px; margin-bottom: 5px; font-size: 0.75rem; color: #999; font-weight: 800; padding-left: 18px; text-transform: uppercase;">
                Categories</div>
            <button class="nav-link" onclick="showSection('categories-list', this)">📂 Categories</button>

            <div
                style="margin-top: 15px; margin-bottom: 5px; font-size: 0.75rem; color: #999; font-weight: 800; padding-left: 18px; text-transform: uppercase;">
                Productes</div>
            <button class="nav-link" onclick="showSection('productes-list', this)">🍔 Productes</button>

            <div
                style="margin-top: 15px; margin-bottom: 5px; font-size: 0.75rem; color: #999; font-weight: 800; padding-left: 18px; text-transform: uppercase;">
                Treballadors</div>
            <button class="nav-link" onclick="showSection('treballadors-list', this)">👥 Treballadors</button>

            <div
                style="margin-top: 15px; margin-bottom: 5px; font-size: 0.75rem; color: #999; font-weight: 800; padding-left: 18px; text-transform: uppercase;">
                Caixa i Vendes</div>
            <button class="nav-link" onclick="showSection('caixa', this)">💵 Tancament de Caixa</button>
            <button class="nav-link" onclick="showSection('comandes', this)">🧾 Historial de Vendes</button>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 0 0 20px 0;">

        <a href="{{ url('/') }}" class="nav-link" style="color: var(--primary-dark)">⬅ Anar al TPV</a>

        <form method="POST" action="{{ route('logout') }}" style="margin-top: auto;">
            @csrf
            <button type="submit" class="btn btn-logout">TANCAR SESSIÓ</button>
        </form>
    </div>



    <div class="main-content">

        @if(session('success'))
            <div class="alert alert-success" style="background:#d1fae5; color:#065f46; padding:15px; border-radius:10px; margin-bottom:20px; font-weight:bold;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:10px; margin-bottom:20px; font-weight:bold;">
                <ul style="margin:0; padding-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="resum" class="section active">
            <h1>📊 Panell de Control</h1>

            {{-- ── KPI Cards ── --}}
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="stat-card border-orange">
                    <h3>💰 Total Avui</h3>
                    <p>{{ number_format($totalAvui, 2) }}€</p>
                    <small>últims 30 dies: {{ number_format($totalMes, 2) }}€</small>
                </div>
                <div class="stat-card border-green">
                    <h3>🧾 Comandes Avui</h3>
                    <p>{{ $comandesComptador }}</p>
                    <small>Tiquet mig: {{ number_format($tiquetMig, 2) }}€</small>
                </div>
                <div class="stat-card border-blue">
                    <h3>💵 Efectiu / Targeta</h3>
                    <p style="font-size:1.4rem;">{{ number_format($efectiuAvui, 2) }}€ /
                        {{ number_format($targetaAvui, 2) }}€
                    </p>
                </div>
                <div class="stat-card border-purple">
                    <h3>🏆 Millor Treballador</h3>
                    <p style="color: var(--success); font-size: 1.4rem;">{{ $millorWorker->name ?? '—' }}</p>
                    <small>per comandes avui</small>
                </div>
            </div>

            {{-- ── Gràfic d'ingressos (7 dies) + Fama de productes ── --}}
            <div class="analytics-grid">
                <div class="card" style="margin-bottom:0;">
                    <p class="card-title">📈 Ingressos Últims 7 Dies</p>
                    <canvas id="chartIngressos" height="160"></canvas>
                </div>
                <div class="card" style="margin-bottom:0;">
                    <p class="card-title">🔥 Productes Més Venuts (Global)</p>
                    @php $maxFama = $topProductes->first()->total_venuts ?? 1; @endphp
                    @forelse($topProductes as $i => $item)
                        @if($item->product)
                            <div class="fame-bar-wrap">
                                <div class="fame-bar-header">
                                    <span>
                                        <span
                                            class="fame-rank {{ $i === 0 ? 'fame-rank-1' : ($i === 1 ? 'fame-rank-2' : ($i === 2 ? 'fame-rank-3' : '')) }}">
                                            {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#' . ($i + 1))) }}
                                        </span>
                                        {{ $item->product->name }}
                                    </span>
                                    <span style="color:#4e73df;">{{ number_format($item->total_venuts, 1) }} un.</span>
                                </div>
                                <div class="fame-bar-track">
                                    <div class="fame-bar-fill"
                                        style="width: {{ round(($item->total_venuts / $maxFama) * 100) }}%"></div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p style="color:#aaa; text-align:center; padding:20px 0;">Encara no hi ha dades de vendes.</p>
                    @endforelse
                </div>
            </div>

            {{-- ── Top per dia de la setmana ── --}}
            <div class="card">
                <p class="card-title">📅 Top Productes per Dia de la Setmana</p>
                <p style="color:#aaa; margin: -10px 0 20px 0; font-size:0.82rem;">Basat en l'historial complet. Dia
                    actual: <strong>{{ $diesCatala[$diaActual] }}</strong>.</p>
                <div class="day-tabs">
                    @foreach($diesCatala as $dow => $nomDia)
                        <button class="day-tab {{ $dow === $diaActual ? 'active today' : '' }}"
                            onclick="showDayTab({{ $dow }}, this)">
                            {{ $diesCurtCatala[$dow] }}{{ $dow === $diaActual ? ' ★' : '' }}
                        </button>
                    @endforeach
                </div>
                @foreach($diesCatala as $dow => $nomDia)
                    <div id="day-content-{{ $dow }}" style="{{ $dow === $diaActual ? '' : 'display:none;' }}">
                        @if($topPerDia[$dow]->isEmpty())
                            <p style="color:#aaa; padding:10px 0;">Sense dades per a {{ $nomDia }}.</p>
                        @else
                            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                                @foreach($topPerDia[$dow] as $j => $item)
                                    @if($item->product)
                                        <div
                                            style="background:#f8f9fe; border-radius:12px; padding:12px 16px; min-width:130px; flex:1;">
                                            <div style="font-size:1.3rem; margin-bottom:4px;">
                                                {{ $j === 0 ? '🥇' : ($j === 1 ? '🥈' : ($j === 2 ? '🥉' : '🍽')) }}
                                            </div>
                                            <strong style="font-size:0.9rem; display:block;">{{ $item->product->name }}</strong>
                                            <span
                                                style="color:#4e73df; font-weight:800; font-size:0.8rem;">{{ number_format($item->total_venuts, 1) }}
                                                venuts</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- ── Hores punta + Mètode pagament ── --}}
            <div class="analytics-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
                {{-- Heatmap d'hores punta --}}
                <div class="card" style="margin-bottom:0;">
                    <p class="card-title">⏰ Hores Punta (últims 30 dies)</p>
                    @php
                        $maxHora = $vestesPerhora->max() ?? 1;
                        $hoursToShow = range(8, 15);
                    @endphp
                    <div class="hour-grid" style="grid-template-columns: repeat(auto-fit, minmax(25px, 1fr));">
                        @foreach($hoursToShow as $h)
                            @php
                                $count = $vestesPerhora->get($h, 0);
                                $pct = $maxHora > 0 ? $count / $maxHora : 0;
                                $alpha = round(0.08 + ($pct * 0.92), 2);
                                $textColor = $pct > 0.45 ? '#fff' : '#4e73df';
                            @endphp
                            <div class="hour-cell"
                                style="background: rgba(78,115,223,{{ $alpha }}); color: {{ $textColor }}; cursor: help;"
                                title="{{ $h }}h: {{ $count }} comandes">
                                {{ sprintf('%02d', $h) }}
                                <span>{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p style="color:#aaa; font-size:0.7rem; margin-top:15px; text-align:center;">Dades basades en els
                        darrers 30 dies (vendes/hora)</p>
                </div>

                {{-- Donut split pagament --}}
                <div class="card" style="margin-bottom:0;">
                    <p class="card-title">💳 Mètode de Pagament (30 dies)</p>
                    @php
                        $efectiuMes = \App\Models\Order::where('status', 'Pagat')->whereDate('created_at', '>=', \Carbon\Carbon::today()->subDays(30))->where('payment_method', 'Efectiu')->sum('total_price');
                        $targetaMes = \App\Models\Order::where('status', 'Pagat')->whereDate('created_at', '>=', \Carbon\Carbon::today()->subDays(30))->where('payment_method', 'Targeta')->sum('total_price');
                    @endphp
                    <div style="display:flex; align-items:center; gap:20px; justify-content: center; flex-wrap: wrap;">
                        <div style="width: 140px; height: 140px; flex-shrink: 0; position: relative;">
                            <canvas id="chartPagament"></canvas>
                        </div>
                        <div style="min-width: 150px; flex: 1;">
                            <div class="payment-pill" style="margin-bottom:12px; padding: 12px;">
                                <div class="amount" style="color:#22c55e; font-size: 1.3rem;">
                                    {{ number_format($efectiuMes, 2) }}€
                                </div>
                                <div class="label" style="font-size: 0.65rem;">💵 Efectiu</div>
                            </div>
                            <div class="payment-pill" style="padding: 12px;">
                                <div class="amount" style="color:#3b82f6; font-size: 1.3rem;">
                                    {{ number_format($targetaMes, 2) }}€
                                </div>
                                <div class="label" style="font-size: 0.65rem;">💳 Targeta</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- TANCAMENT DE CAIXA -->
        <div id="caixa" class="section">
            <h1>💵 Tancament de Caixa</h1>

            <p style="color: #666; font-size: 1rem; margin-bottom: 30px; font-weight: 600;">Control total sobre els
                ingressos de la jornada actual.</p>

            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <div class="stat-card border-orange" style="background: rgba(255, 237, 5, 0.1);">
                    <h3>💰 Total Acumulat Avui</h3>
                    <p>{{ number_format($totalAvui, 2) }}€</p>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--success)">
                    <h3>💵 Efectiu en Calaix</h3>
                    <p style="color: var(--success)">{{ number_format($efectiuAvui, 2) }}€</p>
                    <small style="color: #666;">Diners que han de coincidir manualment a la caixa forta.</small>
                </div>
                <div class="stat-card" style="border-top: 4px solid #3b82f6;">
                    <h3>💳 Pagaments via Targeta</h3>
                    <p style="color: #3b82f6;">{{ number_format($targetaAvui, 2) }}€</p>
                    <small style="color: #666;">Diners enviats al Datàfon/TPV virtual.</small>
                </div>
            </div>

            <div class="card">
                <h2 style="margin-top: 0; margin-bottom: 20px;">Desglossament d'IVA ({{ $ivaPercentatge }}%)</h2>
                <table style="width: 100%;">
                    <thead style="background: #fafafa;">
                        <tr>
                            <th style="padding: 15px;">Concepte</th>
                            <th style="padding: 15px; text-align: right;">Import</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 15px; font-weight: 600;">Base Imposable</td>
                            <td style="padding: 15px; text-align: right;">{{ number_format($baseImposable, 2) }}€</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; font-weight: 600;">Quota IVA ({{ $ivaPercentatge }}%)</td>
                            <td style="padding: 15px; text-align: right; color: var(--danger);">
                                {{ number_format($quotaIva, 2) }}€
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; font-weight: 800; font-size: 1.2rem; border-top: 2px solid #eee;">
                                Total Brut</td>
                            <td
                                style="padding: 15px; text-align: right; font-weight: 800; font-size: 1.2rem; border-top: 2px solid #eee;">
                                {{ number_format($totalAvui, 2) }}€
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="categories-create" class="section">
            <h1>Crear Categoria</h1>
            <div class="card">
                <button class="btn btn-edit" style="margin-bottom: 20px;"
                    onclick="showSection('categories-list', null)">⬅ Tornar a la llista</button>
                <label style="font-weight: 900; margin-bottom: 10px; display: block;">AFEGIR CATEGORIA</label>
                <form action="{{ route('categories.store') }}" method="POST" class="admin-form"
                    style="grid-template-columns: 1fr 120px;">
                    @csrf
                    <div>
                        <input type="text" name="name" placeholder="Ex: Begudes, Hamburgueses..." required>
                    </div>
                    <button type="submit" class="btn btn-add">CREAR</button>
                </form>
            </div>
        </div>

        <div id="categories-list" class="section">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px;">
                <h1 style="margin:0;">Gestió de Categories</h1>
                <button class="btn btn-add" onclick="showSection('categories-create', null)">➕ Nova Categoria</button>
                <script>
                    // Hack per activar la sidebar quan canviem de secció internament
                    function showSectionAndUpdateSidebar(sectionId) {
                        const btn = Array.from(document.querySelectorAll('.nav-link')).find(b => b.getAttribute('onclick')?.includes(sectionId.split('-')[0]));
                        showSection(sectionId, btn);
                    }
                </script>
            </div>
            <div class="card">

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th style="text-align: right;">Accions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                            <tr>
                                <td><strong>{{ $cat->name }}</strong></td>
                                <td style="text-align: right;">
                                    <form action="{{ route('categories.destroy', $cat->id) }}" method="POST"
                                        onsubmit="return confirm('Eliminar?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-delete">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="productes-create" class="section">
            <h1>Crear Producte</h1>
            <div class="card">
                <button class="btn btn-edit" style="margin-bottom: 20px;"
                    onclick="showSection('productes-list', null)">⬅ Tornar a la llista</button>
                <label style="font-weight: 900; margin-bottom: 10px; display: block;">AFEGIR PRODUCTE</label>
                <form action="{{ route('products.store') }}" method="POST" class="admin-form">
                    @csrf
                    <div>
                        <label>Nom</label>
                        <input type="text" name="name" required>
                    </div>
                    <div>
                        <label>Preu (€)</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>
                    <div>
                        <label>Stock (Opcional)</label>
                        <input type="number" name="stock" min="0" step="0.5" placeholder="∞ (Lliure)">
                    </div>
                    <div>
                        <label>Categoria</label>
                        <select name="category_id" required>
                            <option value="">Tria una...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-add">AFEGIR</button>
                </form>
            </div>
        </div>

        <div id="productes-list" class="section">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px;">
                <h1 style="margin:0;">Gestió de Productes</h1>
                <button class="btn btn-add" onclick="showSection('productes-create', null)">➕ Nou Producte</button>
            </div>
            <div class="card">
                <input type="text" id="productSearch" class="search-bar" placeholder="🔍 Cerca per nom de producte..."
                    onkeyup="filterProducts()">
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th>Producte</th>
                            <th>Categoria</th>
                            <th>Preu</th>
                            <th>Stock</th>
                            <th style="text-align: right;">Accions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productes as $product)
                            <tr class="product-row">
                                <td class="prod-name"><strong>{{ $product->name }}</strong></td>
                                <td>
                                    @if($product->categories->count() > 0)
                                        <span class="badge">{{ $product->categories->first()->name }}</span>
                                    @else
                                        <small style="color: gray;">Sense cat.</small>
                                    @endif
                                </td>
                                <td><strong style="color: var(--success)">{{ number_format($product->price, 2) }}€</strong>
                                </td>
                                <td>
                                    @if(!is_null($product->stock))
                                        <span class="badge" style="background:#e6fffa; color:#2c7a7b;">{{ $product->stock }}
                                            <small>un.</small></span>
                                    @else
                                        <small style="color: gray;">--</small>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <button class="btn btn-edit" onclick="toggleEdit('{{ $product->id }}')">Editar</button>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                        onsubmit="return confirm('Eliminar?')" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-delete">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <tr id="edit-{{ $product->id }}" class="edit-row">
                                <td colspan="5">
                                    <form action="{{ route('products.update', $product->id) }}" method="POST"
                                        style="display: flex; gap: 10px; padding: 10px;">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $product->name }}" required
                                            style="flex: 2; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                                        <input type="number" name="price" value="{{ $product->price }}" step="0.01" required
                                            style="width: 80px; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                                        <input type="number" name="stock" value="{{ $product->stock }}" min="0" step="0.5"
                                            placeholder="Lliure"
                                            style="width: 80px; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                                        <select name="category_id" required
                                            style="padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $product->categories->contains($cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-add" style="padding: 5px 15px;">OK</button>
                                        <button type="button" class="btn btn-delete"
                                            onclick="toggleEdit('{{ $product->id }}')" style="padding: 5px 15px;">X</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="treballadors-create" class="section">
            <h1>Crear Treballador</h1>
            <div class="card">
                <button class="btn btn-edit" style="margin-bottom: 20px;"
                    onclick="showSection('treballadors-list', null)">⬅ Tornar a la llista</button>
                <label style="font-weight: 900; margin-bottom: 10px; display: block;">NOU TREBALLADOR</label>
                <form action="{{ route('workers.store') }}" method="POST" class="admin-form"
                    style="grid-template-columns: 1fr 1fr 120px;">
                    @csrf
                    <div>
                        <label>Nom complet</label>
                        <input type="text" name="name" required>
                    </div>
                    @if(empty($adminWorkerId))
                        <div>
                            <label>PIN (4 xifres)</label>
                            <input type="text" name="pin" pattern="\d{4}">
                        </div>
                    @else
                        <div>
                            <label style="color: #ccc;">PIN (Bloquejat)</label>
                            <input type="text" disabled placeholder="Ja hi ha administrador" title="Ja hi ha assignat un PIN a un altre treballador">
                        </div>
                    @endif
                    <button type="submit" class="btn btn-add">AFEGIR</button>
                </form>
            </div>
        </div>

        <div id="treballadors-list" class="section">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px;">
                <h1 style="margin:0;">Gestió de Treballadors</h1>
                <button class="btn btn-add" onclick="showSection('treballadors-create', null)">➕ Nou
                    Treballador</button>
            </div>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>PIN</th>
                            <th style="text-align: right;">Accions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($treballadors as $worker)
                            <tr>
                                <td><strong>{{ $worker->name }}</strong></td>
                                <td>
                                    @if(!is_null($worker->pin) && $worker->pin !== '')
                                        <span class="badge" style="background:#e6fffa; color:#2c7a7b;">✅ ACTIU</span>
                                    @else
                                        <span class="badge" style="background:#fff5f5; color:#c53030;">❌ NO PIN</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <button class="btn btn-edit" onclick="toggleWorkerEdit('{{ $worker->id }}')">Editar</button>
                                    <form action="{{ route('workers.destroy', $worker->id) }}" method="POST"
                                        onsubmit="return confirm('Eliminar?')" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-delete">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <tr id="edit-worker-{{ $worker->id }}" class="edit-row">
                                <td colspan="3">
                                    <form action="{{ route('workers.update', $worker->id) }}" method="POST"
                                        style="display: flex; gap: 10px; padding: 10px; align-items: flex-end;">
                                        @csrf @method('PUT')
                                        <div style="flex: 1; text-align: left;">
                                            <label style="font-size: 0.7rem; font-weight: bold; color: #888; display: block; margin-bottom: 5px;">Nom</label>
                                            <input type="text" name="name" value="{{ $worker->name }}" required
                                                style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd; background: #fff;">
                                        </div>
                                        @if(empty($adminWorkerId) || $adminWorkerId === $worker->id)
                                            <div style="width: 120px; text-align: left;">
                                                <label style="font-size: 0.7rem; font-weight: bold; color: #888; display: block; margin-bottom: 5px;">PIN (4 dígits)</label>
                                                <input type="text" name="pin" value="{{ $worker->pin }}" pattern="\d{4}" maxlength="4"
                                                    placeholder="Ex: 1234"
                                                    style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd; background: #fff;">
                                            </div>
                                        @else
                                            <div style="width: 120px; text-align: left;">
                                                <label style="font-size: 0.7rem; font-weight: bold; color: #ccc; display: block; margin-bottom: 5px;">PIN</label>
                                                <input type="text" disabled placeholder="Bloquejat"
                                                    style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #eee; background: #f9f9f9; color: #999; cursor: not-allowed;" title="Aquest sistema ja té un administrador">
                                            </div>
                                        @endif
                                        <button type="submit" class="btn btn-add" style="padding: 9px 15px; margin-bottom: 1px;">OK</button>
                                        <button type="button" class="btn btn-delete"
                                            onclick="toggleWorkerEdit('{{ $worker->id }}')" style="padding: 9px 15px; margin-bottom: 1px;">X</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="comandes" class="section">
            <h1>Historial de Vendes</h1>
            <div class="card">

                <div class="historial-filters">
                    <div class="historial-filters-row">
                        <div class="historial-payment-block">
                            <span class="historial-block-label">Filtrar per mètode</span>
                            <div class="historial-payment-pills">
                                <a href="?payment=all{{ $startDate ? '&start_date='.$startDate : '' }}{{ $endDate ? '&end_date='.$endDate : '' }}#comandes" class="filter-pill {{ empty($paymentFilter) || $paymentFilter === 'all' ? 'active' : '' }}" style="text-decoration:none;">🧾 Tots</a>
                                <a href="?payment=Efectiu{{ $startDate ? '&start_date='.$startDate : '' }}{{ $endDate ? '&end_date='.$endDate : '' }}#comandes" class="filter-pill pill-efectiu {{ $paymentFilter === 'Efectiu' ? 'active' : '' }}" style="text-decoration:none;">💵 Efectiu</a>
                                <a href="?payment=Targeta{{ $startDate ? '&start_date='.$startDate : '' }}{{ $endDate ? '&end_date='.$endDate : '' }}#comandes" class="filter-pill pill-targeta {{ $paymentFilter === 'Targeta' ? 'active' : '' }}" style="text-decoration:none;">💳 Targeta</a>
                            </div>
                        </div>
                        <form class="historial-date-compact-form" action="{{ url('/admin') }}" method="GET" id="historial-date-filter-form">
                            <input type="hidden" name="payment" value="{{ $paymentFilter ?? 'all' }}">
                            <input type="hidden" name="start_date" id="historial-hid-start" value="{{ $startDate ?? '' }}">
                            <input type="hidden" name="end_date" id="historial-hid-end" value="{{ $endDate ?? '' }}">
                            <span class="historial-block-label" style="margin:0;">Període</span>
                            <div class="historial-date-chips-row">
                                <button type="button" class="date-wheel-chip" id="historial-chip-start" title="Des de (inclòs)">—</button>
                                <span class="date-chip-sep">al</span>
                                <button type="button" class="date-wheel-chip" id="historial-chip-end" title="Fins a (inclòs)">—</button>
                            </div>
                            <div class="historial-date-actions-inline">
                                <button type="submit" class="btn btn-add btn-touch-historial" onclick="window.__initialHash='comandes'">Aplicar període</button>
                                @if($startDate || $endDate)
                                    <a href="?payment={{ $paymentFilter ?? 'all' }}#comandes" class="btn btn-delete btn-touch-historial" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;" title="Netejar dates">Netejar dates</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <table id="vendesTable">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Hora</th>
                            <th>Treballador</th>
                            <th>Total</th>
                            <th>Mètode</th>
                            <th style="text-align: right;">Accions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($darreresVendes as $venda)
                            {{-- Fila principal clicable --}}
                            <tr class="venda-row" data-payment="{{ $venda->payment_method }}" onclick="toggleVenda({{ $venda->id }}, this)">
                                <td style="width:24px; color:#bbb;"><span class="chevron-icon">▼</span></td>
                                <td>{{ $venda->created_at->format('H:i') }}h</td>
                                <td><strong>{{ $venda->worker->name ?? 'Sistema' }}</strong></td>
                                <td><strong style="color:var(--success)">{{ number_format($venda->total_price, 2) }}€</strong></td>
                                <td>
                                    @if($venda->payment_method === 'Targeta')
                                        <span class="badge" style="background:#eff6ff; color:#3b82f6;">💳 Targeta</span>
                                    @else
                                        <span class="badge" style="background:#f0fdf4; color:#16a34a;">💵 Efectiu</span>
                                    @endif
                                </td>
                                <td style="text-align: right;" onclick="event.stopPropagation()">
                                    <form action="{{ url('/admin/orders/' . $venda->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-delete">ANUL·LAR</button>
                                    </form>
                                </td>
                            </tr>
                            {{-- Fila desplegable amb desglose --}}
                            <tr class="venda-detail-row" id="detail-{{ $venda->id }}" data-payment="{{ $venda->payment_method }}">
                                <td colspan="6">
                                    <div class="venda-detail-inner" id="inner-{{ $venda->id }}">
                                        @if($venda->items->isEmpty())
                                            <p style="color:#aaa; font-size:0.85rem; margin:0;">Sense productes registrats.</p>
                                        @else
                                            @foreach($venda->items as $item)
                                                <div class="detail-item-row">
                                                    <span>
                                                        <span class="detail-qty">×{{ $item->quantity }}</span>
                                                        {{ $item->product->name ?? '(Producte eliminat)' }}
                                                        @if($item->notes)
                                                            <small style="color:#aaa; margin-left:6px;">— {{ $item->notes }}</small>
                                                        @endif
                                                    </span>
                                                    <span class="detail-price">{{ number_format($item->price_at_sale * $item->quantity, 2) }}€</span>
                                                </div>
                                            @endforeach
                                            <div style="margin-top:10px; padding-top:10px; border-top:2px solid #e0e7ff; display:flex; justify-content:flex-end;">
                                                <strong style="font-size:0.9rem;">Total: <span style="color:var(--success);">{{ number_format($venda->total_price, 2) }}€</span></strong>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px;">Cap venda avui.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($darreresVendes->hasPages())
                    <div style="margin-top: 25px; display: flex; justify-content: center;">
                        <nav class="pagination-admin" aria-label="Paginació historial">
                            {{-- Previous Page Link --}}
                            @if ($darreresVendes->onFirstPage())
                                <span style="background: #fafafa; color: #aaa; font-weight: bold; border-right: 1px solid #e2e8f0;">« Ant.</span>
                            @else
                                <a href="{{ $darreresVendes->previousPageUrl() }}" style="background: #fff; color: #4e73df; font-weight: bold; text-decoration: none; border-right: 1px solid #e2e8f0;">« Ant.</a>
                            @endif

                            {{-- Next Page Link --}}
                            @if ($darreresVendes->hasMorePages())
                                <a href="{{ $darreresVendes->nextPageUrl() }}" style="background: #fff; color: #4e73df; font-weight: bold; text-decoration: none;">Seg. »</a>
                            @else
                                <span style="background: #fafafa; color: #aaa; font-weight: bold;">Seg. »</span>
                            @endif
                        </nav>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <div id="date-wheel-overlay" class="date-wheel-overlay" aria-hidden="true" role="presentation">
        <div class="date-wheel-backdrop" aria-hidden="true"></div>
        <div class="date-wheel-sheet" role="dialog" aria-modal="true" aria-labelledby="date-wheel-title">
            <div class="date-wheel-header">
                <button type="button" class="date-wheel-header-btn" id="date-wheel-cancel">Cancel·lar</button>
                <span id="date-wheel-title" class="date-wheel-title">Data</span>
                <button type="button" class="date-wheel-header-btn date-wheel-header-primary" id="date-wheel-ok">D'acord</button>
            </div>
            <div class="date-wheel-columns">
                <div class="date-wheel-col">
                    <span class="date-wheel-label">Dia</span>
                    <div class="date-wheel-window">
                        <div class="date-wheel-highlight" aria-hidden="true"></div>
                        <div class="date-wheel-scroll" id="wheel-day"></div>
                    </div>
                </div>
                <div class="date-wheel-col">
                    <span class="date-wheel-label">Mes</span>
                    <div class="date-wheel-window">
                        <div class="date-wheel-highlight" aria-hidden="true"></div>
                        <div class="date-wheel-scroll" id="wheel-month"></div>
                    </div>
                </div>
                <div class="date-wheel-col">
                    <span class="date-wheel-label">Any</span>
                    <div class="date-wheel-window">
                        <div class="date-wheel-highlight" aria-hidden="true"></div>
                        <div class="date-wheel-scroll" id="wheel-year"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(id, element) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            element.classList.add('active');
            // Usem replaceState per no fer scroll automàtic de l'ancla
            history.replaceState(null, '', '#' + id);
            window.scrollTo({ top: 0, behavior: 'instant' });
        }

        function filterProducts() {
            let input = document.getElementById('productSearch').value.toLowerCase();
            let rows = document.querySelectorAll('.product-row');
            rows.forEach(row => {
                let name = row.querySelector('.prod-name').innerText.toLowerCase();
                row.style.display = name.includes(input) ? "table-row" : "none";
            });
        }

        function toggleEdit(id) {
            let editRow = document.getElementById('edit-' + id);
            editRow.classList.toggle('active');
        }

        function toggleWorkerEdit(id) {
            let editRow = document.getElementById('edit-worker-' + id);
            editRow.classList.toggle('active');
        }

        // El filtre ara navega per backend mitjançant enllaços de query string.

        // ── Accordion de desglose de comanda ──
        let openVendaId = null;
        function toggleVenda(id, rowEl) {
            const inner = document.getElementById('inner-' + id);
            const isOpen = inner.classList.contains('open');
            // Tancar l'anterior si n'hi ha un obert
            if (openVendaId && openVendaId !== id) {
                const prevInner = document.getElementById('inner-' + openVendaId);
                const prevRow = document.querySelector('.venda-row[onclick*="toggleVenda(' + openVendaId + ',"]');
                if (prevInner) prevInner.classList.remove('open');
                if (prevRow) prevRow.classList.remove('expanded');
            }
            // Obrir/tancar el clicat
            if (isOpen) {
                inner.classList.remove('open');
                rowEl.classList.remove('expanded');
                openVendaId = null;
            } else {
                inner.classList.add('open');
                rowEl.classList.add('expanded');
                openVendaId = id;
            }
        }

        // ── Selector de dia (Top productes per dia) ──
        function showDayTab(dow, btn) {
            document.querySelectorAll('[id^="day-content-"]').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.day-tab').forEach(b => b.classList.remove('active'));
            document.getElementById('day-content-' + dow).style.display = 'block';
            btn.classList.add('active');
        }

        // ── Historial: selector de data tipus roda (tàctil) ──
        (function historialDateWheel() {
            const ITEM_H = 44;
            const PAD = 88;
            const MONTHS_CA = ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];
            const YEAR_MIN = 2020;
            const YEAR_MAX = 2035;

            let wheelTarget = null;
            let scrollTMY = null;
            let scrollTD = null;
            let suppressWheelScroll = false;

            function daysInMonth(y, m) {
                return new Date(y, m + 1, 0).getDate();
            }

            function pad2(n) {
                return String(n).padStart(2, '0');
            }

            function isoFromParts(y, m, d) {
                return `${y}-${pad2(m + 1)}-${pad2(d)}`;
            }

            function parseISOToParts(s) {
                if (!s || !/^\d{4}-\d{2}-\d{2}$/.test(s)) return null;
                const [y, mo, d] = s.split('-').map(x => parseInt(x, 10));
                return { y, m: mo - 1, d };
            }

            function formatChip(s) {
                if (!s) return 'Tria';
                const p = parseISOToParts(s);
                if (!p) return 'Tria';
                return `${pad2(p.d)}/${pad2(p.m + 1)}/${p.y}`;
            }

            function itemCount(scrollEl) {
                return scrollEl.querySelectorAll('.wheel-item').length;
            }

            function getIndex(scrollEl) {
                const n = itemCount(scrollEl);
                if (!n) return 0;
                const i = Math.round(scrollEl.scrollTop / ITEM_H);
                return Math.max(0, Math.min(n - 1, i));
            }

            function getValueAtIndex(scrollEl, idx) {
                const items = scrollEl.querySelectorAll('.wheel-item');
                return items[idx] ? items[idx].dataset.value : null;
            }

            function snapScroll(scrollEl, instant) {
                const n = itemCount(scrollEl);
                if (!n) return;
                const i = getIndex(scrollEl);
                const top = i * ITEM_H;
                if (instant) scrollEl.scrollTop = top;
                else scrollEl.scrollTo({ top, behavior: 'smooth' });
            }

            function fillScrollColumn(scrollEl, labels, values, selectedVal, onDone) {
                scrollEl.innerHTML = '';
                const topPad = document.createElement('div');
                topPad.className = 'wheel-pad';
                topPad.style.height = PAD + 'px';
                scrollEl.appendChild(topPad);
                let selIdx = 0;
                values.forEach((v, i) => {
                    const div = document.createElement('div');
                    div.className = 'wheel-item';
                    div.textContent = labels[i];
                    div.dataset.value = String(v);
                    if (v === selectedVal || String(v) === String(selectedVal)) selIdx = i;
                    scrollEl.appendChild(div);
                });
                const botPad = document.createElement('div');
                botPad.className = 'wheel-pad';
                botPad.style.height = PAD + 'px';
                scrollEl.appendChild(botPad);
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        scrollEl.scrollTop = selIdx * ITEM_H;
                        if (typeof onDone === 'function') onDone();
                    });
                });
            }

            function readPickedISO() {
                const wy = document.getElementById('wheel-year');
                const wm = document.getElementById('wheel-month');
                const wd = document.getElementById('wheel-day');
                [wy, wm, wd].forEach(el => snapScroll(el, true));
                const y = parseInt(getValueAtIndex(wy, getIndex(wy)), 10);
                const m = parseInt(getValueAtIndex(wm, getIndex(wm)), 10);
                let d = parseInt(getValueAtIndex(wd, getIndex(wd)), 10);
                const maxD = daysInMonth(y, m);
                if (d > maxD) d = maxD;
                return isoFromParts(y, m, d);
            }

            function onMonthYearScroll() {
                if (suppressWheelScroll) return;
                clearTimeout(scrollTMY);
                scrollTMY = setTimeout(() => {
                    const wy = document.getElementById('wheel-year');
                    const wm = document.getElementById('wheel-month');
                    const wd = document.getElementById('wheel-day');
                    snapScroll(wy, true);
                    snapScroll(wm, true);
                    const y = parseInt(getValueAtIndex(wy, getIndex(wy)), 10);
                    const m = parseInt(getValueAtIndex(wm, getIndex(wm)), 10);
                    let curD = parseInt(getValueAtIndex(wd, getIndex(wd)), 10) || 1;
                    const maxD = daysInMonth(y, m);
                    if (curD > maxD) curD = maxD;
                    const days = [];
                    const labels = [];
                    for (let i = 1; i <= maxD; i++) {
                        days.push(i);
                        labels.push(String(i));
                    }
                    fillScrollColumn(wd, labels, days, curD, () => snapScroll(wd, true));
                }, 100);
            }

            function onDayScroll() {
                if (suppressWheelScroll) return;
                clearTimeout(scrollTD);
                scrollTD = setTimeout(() => {
                    snapScroll(document.getElementById('wheel-day'), false);
                }, 70);
            }

            function openDateWheel(which) {
                wheelTarget = which;
                const hid = document.getElementById(which === 'start' ? 'historial-hid-start' : 'historial-hid-end');
                const title = document.getElementById('date-wheel-title');
                title.textContent = which === 'start' ? 'Data d\'inici' : 'Data de fi';
                const p = parseISOToParts(hid.value);
                let y;
                let m;
                let d;
                if (p) {
                    y = p.y;
                    m = p.m;
                    d = p.d;
                } else {
                    const t = new Date();
                    y = t.getFullYear();
                    m = t.getMonth();
                    d = t.getDate();
                }
                const years = [];
                const yLabels = [];
                for (let yy = YEAR_MIN; yy <= YEAR_MAX; yy++) {
                    years.push(yy);
                    yLabels.push(String(yy));
                }
                const wm = document.getElementById('wheel-month');
                const wd = document.getElementById('wheel-day');
                fillScrollColumn(document.getElementById('wheel-year'), yLabels, years, y);
                const monthVals = [...Array(12).keys()];
                fillScrollColumn(wm, MONTHS_CA, monthVals, m);
                const maxD = daysInMonth(y, m);
                if (d > maxD) d = maxD;
                const days = [];
                const dLabels = [];
                for (let i = 1; i <= maxD; i++) {
                    days.push(i);
                    dLabels.push(String(i));
                }
                fillScrollColumn(wd, dLabels, days, d);
                const ov = document.getElementById('date-wheel-overlay');
                ov.classList.add('is-open');
                ov.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                suppressWheelScroll = true;
                setTimeout(() => { suppressWheelScroll = false; }, 400);
            }

            function closeDateWheel(save) {
                const ov = document.getElementById('date-wheel-overlay');
                if (save && wheelTarget) {
                    const iso = readPickedISO();
                    const hid = document.getElementById(wheelTarget === 'start' ? 'historial-hid-start' : 'historial-hid-end');
                    hid.value = iso;
                    document.getElementById(wheelTarget === 'start' ? 'historial-chip-start' : 'historial-chip-end').textContent = formatChip(iso);
                }
                ov.classList.remove('is-open');
                ov.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                wheelTarget = null;
            }

            function syncHistorialChipsFromHidden() {
                const hs = document.getElementById('historial-hid-start');
                const he = document.getElementById('historial-hid-end');
                const cs = document.getElementById('historial-chip-start');
                const ce = document.getElementById('historial-chip-end');
                if (cs && hs) cs.textContent = formatChip(hs.value);
                if (ce && he) ce.textContent = formatChip(he.value);
            }

            document.addEventListener('DOMContentLoaded', () => {
                syncHistorialChipsFromHidden();
                const chipS = document.getElementById('historial-chip-start');
                const chipE = document.getElementById('historial-chip-end');
                if (chipS) chipS.addEventListener('click', () => openDateWheel('start'));
                if (chipE) chipE.addEventListener('click', () => openDateWheel('end'));
                document.getElementById('date-wheel-cancel')?.addEventListener('click', () => closeDateWheel(false));
                document.getElementById('date-wheel-ok')?.addEventListener('click', () => closeDateWheel(true));
                document.querySelector('#date-wheel-overlay .date-wheel-backdrop')?.addEventListener('click', () => closeDateWheel(false));

                document.getElementById('wheel-year')?.addEventListener('scroll', onMonthYearScroll, { passive: true });
                document.getElementById('wheel-month')?.addEventListener('scroll', onMonthYearScroll, { passive: true });
                document.getElementById('wheel-day')?.addEventListener('scroll', onDayScroll, { passive: true });
            });
        })();

        // ── Charts (Chart.js) ──
        window.addEventListener('DOMContentLoaded', () => {
            // Restaurem la secció des del hash inicial (ja sense scroll automàtic del navegador)
            const hash = window.__initialHash || '';
            if (hash) {
                const targetButton = document.querySelector(`button[onclick*="${hash}"]`);
                if (targetButton) showSection(hash, targetButton);
            }
            window.scrollTo({ top: 0, behavior: 'instant' });
            // Gràfic d'ingressos setmanals
            const ctxLine = document.getElementById('chartIngressos');
            if (ctxLine) {
                new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($labelsSetmana) !!},
                        datasets: [{
                            label: 'Ingressos (€)',
                            data: {!! json_encode($ingressosSetmana) !!},
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78,115,223,0.08)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#4e73df',
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            borderWidth: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ' ' + ctx.parsed.y.toFixed(2) + ' €'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f4f4f8' },
                                ticks: { callback: v => v + '€' }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Gràfic de donut (pagament)
            const ctxDonut = document.getElementById('chartPagament');
            if (ctxDonut) {
                const efectiu = {{ $efectiuMes ?? 0 }};
                const targeta = {{ $targetaMes ?? 0 }};
                new Chart(ctxDonut, {
                    type: 'doughnut',
                    data: {
                        labels: ['Efectiu', 'Targeta'],
                        datasets: [{
                            data: [efectiu || 0.001, targeta || 0.001],
                            backgroundColor: ['#22c55e', '#3b82f6'],
                            borderWidth: 0,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ' ' + ctx.parsed.toFixed(2) + ' €'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>

</body>

</html>