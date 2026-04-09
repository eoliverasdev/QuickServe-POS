<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TPV Premium - La Cresta</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #4e73df;
            --bg-body: #f4f7fe;
            --bg-card: #ffffff;
            --text-main: #1b2559;
            --text-secondary: #a3abb9;
            --accent-yellow: #ffed05;
            --danger: #ff4d4d;
        }

        body {
            margin: 0;
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* --- Estructura Principal --- */
        .app-wrapper {
            display: grid;
            grid-template-columns: 80px 1fr 400px;
            height: 100vh;
            overflow: hidden;
        }

        /* --- Menú Lateral --- */
        .navbar-icons {
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            border-right: 1px solid #e9edf7;
        }

        .nav-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin-bottom: 20px;
            color: var(--text-secondary);
            transition: 0.3s;
            cursor: pointer;
            text-decoration: none;
            background: transparent;
            border: none;
        }

        .nav-icon.active,
        .nav-icon:hover {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
        }

        /* --- Secció Central --- */
        .main-content {
            padding: 30px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        /* --- Categories --- */
        .categories-grid {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .cat-card {
            background: #fff;
            padding: 15px 25px;
            border-radius: 18px;
            min-width: 100px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            border: 1px solid transparent;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }

        .cat-card.active {
            background: var(--primary);
            color: #fff;
        }

        /* --- Productes --- */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: #fff;
            border-radius: 20px;
            padding: 15px;
            position: relative;
            transition: 0.3s;
            border: 1px solid transparent;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.02);
        }

        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .product-img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 12px;
        }

        .price-tag {
            color: var(--text-main);
            font-weight: 800;
            font-size: 1.1rem;
        }

        .card-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
            background: #f8f9fe;
            padding: 5px;
            border-radius: 12px;
        }

        .btn-card-qty {
            background: #fff;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: 0.2s;
        }

        .btn-card-qty:hover {
            background: var(--primary);
            color: #fff;
        }

        .qty-display {
            font-weight: 800;
            font-size: 0.9rem;
            min-width: 20px;
            text-align: center;
        }

        /* --- Botons Pagament --- */
        .method-btn {
            flex: 1;
            background: #fff;
            border: 1px solid #e9edf7;
            padding: 12px;
            border-radius: 15px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-weight: 600;
            color: var(--text-secondary);
            transition: 0.3s;
        }

        .method-btn.active {
            border-color: var(--primary);
            background: #f0f3ff;
            color: var(--primary);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.15);
        }

        .worker-pill-btn {
            background: #f8f9fe;
            border: 1px solid #e9edf7;
            padding: 8px 16px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.85rem;
            color: #666;
            transition: 0.2s;
        }

        .worker-pill-btn.active {
            background: #4e73df;
            color: #fff;
            border-color: #4e73df;
            box-shadow: 0 4px 10px rgba(78, 115, 223, 0.2);
        }

        .worker-pill-btn:hover {
            background: #f0f3ff;
            color: #4e73df;
            border-color: #4e73df;
        }

        .time-quick-btn {
            background: #fff;
            border: 1px solid #e9edf7;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s;
            color: #4e73df;
        }

        .time-quick-btn:hover {
            background: #4e73df;
            color: #fff;
        }

        /* --- Tiquet --- */
        .invoice-sidebar {
            background: #fff;
            padding: 30px;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #e9edf7;
            box-sizing: border-box;
            max-height: 100vh;
        }

        .invoice-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            flex-shrink: 0;
        }

        .cart-items-list {
            flex-grow: 1;
            overflow-y: auto;
            min-height: 0;
            padding-right: 8px;
        }

        /* Scrollbar (Slider estètic) per a la comanda */
        .cart-items-list::-webkit-scrollbar {
            width: 6px;
        }

        .cart-items-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .cart-items-list::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }

        .cart-items-list::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .cart-item {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f4f7fe;
        }

        .cart-item-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cart-item img {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            object-fit: cover;
        }

        .item-info {
            flex-grow: 1;
        }

        .item-info h4 {
            margin: 0;
            font-size: 0.9rem;
        }

        .item-info p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8f9fe;
            padding: 8px 12px;
            border-radius: 12px;
        }

        .btn-qty {
            background: #fff;
            border: 1px solid #e9edf7;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .btn-qty:hover {
            background: var(--primary);
            color: #fff;
        }

        .btn-remove {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
        }

        .payment-summary {
            background: #f8f9fe;
            padding: 20px;
            border-radius: 20px;
            margin-top: 20px;
            flex-shrink: 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: var(--text-secondary);
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #cbd5e0;
            font-weight: 800;
            font-size: 1.3rem;
        }

        .btn-place-order {
            background: var(--primary);
            color: #fff;
            border: none;
            width: 100%;
            padding: 18px;
            border-radius: 15px;
            font-weight: 800;
            margin-top: 20px;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(78, 115, 223, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .btn-place-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(78, 115, 223, 0.3);
        }

        .btn-place-order:active {
            transform: translateY(0);
            box-shadow: 0 6px 12px rgba(78, 115, 223, 0.2);
            opacity: 0.9;
        }

        /* --- Modal --- */
        #user-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(8px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 40px;
            border-radius: 30px;
            width: 400px;
            text-align: center;
        }

        .worker-btn {
            width: 100%;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #eee;
            border-radius: 15px;
            background: #fbfbfb;
            cursor: pointer;
            font-weight: 600;
            color: #000;
            transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        }

        .worker-btn:hover {
            background: var(--primary);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(78, 115, 223, 0.25);
        }

        .option-label {
            flex: 1;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            font-weight: bold;
            font-size: 0.9rem;
            transition: 0.2s;
            background: #fff;
            color: #666;
        }

        .option-label.selected {
            border-color: var(--primary);
            background: rgba(255, 237, 5, 0.15);
            color: #000;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 800;
            backdrop-filter: blur(4px);
            z-index: 10;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stock-out {
            background: rgba(255, 77, 77, 0.9);
        }

    </style>
</head>

<body>

    <div class="app-wrapper">
        <aside class="navbar-icons">
            <div class="nav-icon active">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <button class="nav-icon" onclick="openPendingPreordersModal()" title="Encàrrecs" style="position:relative;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
                <span id="preorder-badge"
                    style="display:none; position:absolute; top:-5px; right:-5px; background:var(--danger); color:white; border-radius:50%; padding:2px 5px; font-weight:bold; font-size:10px;">0</span>
            </button>
            <button class="nav-icon" onclick="openParkedTicketsModal()" title="Tickets Aparcats" style="position:relative;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                </svg>
                <span id="parked-badge"
                    style="display:none; position:absolute; top:-5px; right:-5px; background:var(--primary); color:#000; border-radius:50%; padding:2px 6px; font-weight:900; font-size:10px;">0</span>
            </button>
            <button class="nav-icon" onclick="openAdminPinModal()" title="Admin">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20v-6M9 20v-10M15 20v-4M3 20h18"></path>
                </svg>
            </button>
            <div style="margin-top: auto;">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-icon" style="color: var(--danger);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <main class="main-content">
            <section>
                <div class="categories-grid">
                    <div class="cat-card active" onclick="filterCategory('all', this)">
                        <strong>Tots</strong>
                    </div>
                    @foreach($categories as $category)
                        <div class="cat-card" onclick="filterCategory('{{ $category->name }}', this)">
                            <strong>{{ $category->name }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="products-grid">
                @foreach($products as $product)
                    <div class="product-card" id="prod-card-{{ $product->id }}" data-id="{{ $product->id }}"
                        data-name="{{ $product->name }}" data-price="{{ $product->price }}"
                        data-img="{{ asset($product->image_path) }}" data-stock="{{ $product->stock ?? 'null' }}"
                        data-fame="{{ $product->sales_count ?? 0 }}"
                        data-categories="{{ $product->categories->pluck('name')->join(' ') }}">

                        @if(!is_null($product->stock))
                            <div class="stock-badge {{ $product->stock <= 0 ? 'stock-out' : '' }}">
                                @if($product->stock <= 0)
                                    ⛔ Esgotat
                                @else
                                    {{ $product->stock }} restants
                                @endif
                            </div>
                        @endif

                        <img src="{{ asset($product->image_path) }}" class="product-img"
                            onclick="changeQtyFromCard({{ $product->id }}, 1)" style="cursor: pointer;">

                        <h4 style="margin: 0 0 5px 0; cursor: pointer;" onclick="changeQtyFromCard({{ $product->id }}, 1)">
                            {{ $product->name }}
                        </h4>

                        <div class="price-tag">{{ number_format($product->price, 2) }}€</div>

                        <div class="card-controls">
                            <button class="btn-card-qty" onclick="changeQtyFromCard({{ $product->id }}, -1)">-</button>
                            <span class="qty-display" id="card-qty-{{ $product->id }}">0</span>
                            <button class="btn-card-qty" style="background: var(--primary); color: #fff;"
                                onclick="changeQtyFromCard({{ $product->id }}, 1)">+</button>
                        </div>
                    </div>
                @endforeach
            </section>
        </main>

        <aside class="invoice-sidebar">
            <div style="display:flex; justify-content:flex-start; align-items:center; margin-bottom: 25px; flex-shrink:0; gap:15px;">
                <h2 class="invoice-title">Ordre actual</h2>
                <button id="btn-clear-cart" onclick="clearCart(true)" style="display:none; background:#fff0f0; color:var(--danger); border:1px solid var(--danger); padding:6px 10px; border-radius:8px; font-weight:800; cursor:pointer; font-size:0.8rem; transition:background 0.2s, color 0.2s; align-items:center; gap:6px;" onmouseover="this.style.background='var(--danger)'; this.style.color='#fff';" onmouseout="this.style.background='#fff0f0'; this.style.color='var(--danger)';">
                    <span style="font-size:0.9rem;">🗑️</span> Buidar
                </button>
            </div>
            <div class="cart-items-list" id="cart-list"></div>

            <div class="payment-summary">
                <div class="summary-row">
                    <span>Base Imposable</span>
                    <span id="sub-total">0.00€</span>
                </div>
                <div class="summary-row">
                    <span>IVA (21%)</span>
                    <span id="iva">0.00€</span>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span id="cart-total">0.00€</span>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button class="btn-place-order" onclick="openUserModal(false)"
                        style="flex:1; padding:15px; font-size:0.95rem;">💵 Venda</button>
                    <button class="btn-place-order" onclick="openUserModal(true)"
                        style="flex:1; padding:15px; font-size:0.95rem; background:#fff; border:2px solid var(--primary); color:var(--dark);">📝
                        Encàrrec</button>
                </div>
                <button class="btn-place-order" onclick="parkCurrentTicket()"
                    style="background:#f4f7fe; color:#4e73df; border:2px dashed #4e73df; padding:12px; margin-top:10px; font-size:0.85rem; box-shadow:none;">
                    ⏸️ Aparcar Ticket
                </button>
            </div>
        </aside>
    </div>

    <div id="chicken-modal"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: center;">
        <div class="modal-content"
            style="background: #fff; padding: 30px; border-radius: 20px; width: 380px; text-align: left;">
            <h2 style="margin-top: 0; margin-bottom: 20px;" id="chicken-modal-title">Opcions de cocció</h2>

            <input type="hidden" id="chicken-modal-id">
            <input type="hidden" id="chicken-modal-name">
            <input type="hidden" id="chicken-modal-price">
            <input type="hidden" id="chicken-modal-img">

            <h4 style="margin-bottom: 12px; color: #666; text-transform: uppercase; font-size: 0.8rem;">Opcions de suc
            </h4>
            <div style="display: flex; gap: 10px; margin-bottom: 25px;">
                <label class="option-label">
                    <input type="radio" name="chicken_suc" value="Amb suc" style="display: none;"
                        onchange="updateChickenOptionsUI()">
                    Amb Suc
                </label>
                <label class="option-label selected">
                    <input type="radio" name="chicken_suc" value="Sense suc" checked style="display: none;"
                        onchange="updateChickenOptionsUI()">
                    Sense Suc
                </label>
            </div>

            <h4 style="margin-bottom: 12px; color: #666; text-transform: uppercase; font-size: 0.8rem;">Punt de cocció
            </h4>
            <div style="display: flex; gap: 10px; margin-bottom: 25px;">
                <label class="option-label">
                    <input type="radio" name="chicken_cucci" value="Normal" checked style="display: none;"
                        onchange="updateChickenOptionsUI()">
                    Normal
                </label>
                <label class="option-label">
                    <input type="radio" name="chicken_cucci" value="Poc cuit" style="display: none;"
                        onchange="updateChickenOptionsUI()">
                    Poc Cuit
                </label>
                <label class="option-label">
                    <input type="radio" name="chicken_cucci" value="Molt cuit" style="display: none;"
                        onchange="updateChickenOptionsUI()">
                    Molt Cuit
                </label>
            </div>

            <button class="btn-place-order" style="margin-top: 0; padding: 15px;" onclick="addChickenToCart()">Afegir a
                la comanda</button>
            <button type="button"
                style="width: 100%; border: none; background: none; color: #999; margin-top: 15px; cursor: pointer; font-weight: 600;"
                onclick="closeChickenModal()">Cancel·lar</button>
        </div>
    </div>

    <div id="user-modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 20px;">Qui està gestionant?</h2>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                @foreach($workers as $worker)
                    <button class="worker-btn" data-worker-id="{{ $worker->id }}"
                        onclick="selectWorker({{ $worker->id }}, this)">{{ $worker->name }}</button>
                @endforeach
            </div>

            <div id="preorder-fields" style="display:none; margin-top: 20px; text-align:left;">
                <label style="font-weight:bold; margin-bottom:8px; display:block;">Hora de recollida:</label>
                <div style="display: flex; gap: 5px; margin-bottom: 10px; flex-wrap: wrap;">
                    <button type="button" class="time-quick-btn" onclick="adjustTime(-60)">-60m</button>
                    <button type="button" class="time-quick-btn" onclick="adjustTime(-30)">-30m</button>
                    <button type="button" class="time-quick-btn" onclick="adjustTime(-15)">-15m</button>
                    <button type="button" class="time-quick-btn" onclick="adjustTime(0)">Ara</button>
                    <button type="button" class="time-quick-btn" onclick="adjustTime(15)">+15m</button>
                    <button type="button" class="time-quick-btn" onclick="adjustTime(30)">+30m</button>
                    <button type="button" class="time-quick-btn" onclick="adjustTime(60)">+60m</button>
                </div>
                <input type="time" id="preorder-time"
                    style="width:100%; padding: 15px; font-size: 1.2rem; font-weight: 800; border-radius:12px; border:2px solid #e9edf7; margin-bottom:15px; color: #4e73df;"
                    required step="900">

                <label style="font-weight:bold; display:block; margin-bottom:5px;">Nom del Client (Opcional):</label>
                <input type="text" id="preorder-name"
                    style="width:100%; padding: 10px; border-radius:10px; border:1px solid #ccc;">
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <button class="btn-place-order" id="btn-final-confirm" onclick="openPaymentMethodModal()"
                    style="display:none; text-transform:uppercase;">Continuar</button>
                <br><button onclick="closeUserModal()"
                    style="border:none; background:none; color:#999; margin-top:10px; cursor:pointer;">Cancel·lar</button>
            </div>
        </div>
    </div>

    <div id="admin-pin-modal"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 4000; justify-content: center; align-items: center;">
        <div class="modal-content"
            style="background:#fff; width:350px; padding:30px; border-radius:15px; text-align:center;">
            <h2 style="margin-top:0; color:var(--text-main);">Accés Administració</h2>
            <p style="color:#666; font-size:0.9rem; margin-bottom:20px;">Introdueix el PIN d'encarregat.</p>

            <input type="password" id="admin-pin-input" inputmode="numeric" pattern="\d*" maxlength="4"
                placeholder="****"
                style="text-align:center; font-size:2rem; letter-spacing:10px; width:150px; padding:10px; border-radius:10px; border:2px solid var(--primary); margin-bottom:20px; outline:none;">

            <button class="btn-place-order" onclick="verifyAdminPin()"
                style="width:100%; display:block; padding:15px; font-size:1.1rem; margin-bottom:10px;">Accedir</button>
            <button style="border:none; background:none; color:#999; cursor:pointer; font-weight:bold;"
                onclick="document.getElementById('admin-pin-modal').style.display='none'">Cancel·lar</button>
        </div>
    </div>

    <div id="payment-modal"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.35); backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background:#fff; border-radius:24px; width:520px; max-height:90vh; overflow-y:auto; padding:35px; box-shadow: 0 25px 60px rgba(0,0,0,0.15);">

            <h2 style="margin:0 0 20px 0; font-size:1.4rem;">💳 Resum del Pagament</h2>

            {{-- Resum de productes --}}
            <div style="background:#f8f9fe; border-radius:16px; padding:16px; margin-bottom:18px;">
                <p style="font-size:0.7rem; font-weight:800; color:#aaa; text-transform:uppercase; margin:0 0 12px 0;">Productes de la comanda</p>
                <div id="payment-order-summary" style="display:flex; flex-direction:column; gap:6px;"></div>
                <div style="border-top:1px dashed #dde; margin:12px 0 8px;"></div>
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; color:#888; margin-bottom:4px;">
                    <span>Base Imposable</span><span id="payment-base">0.00€</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; color:#888; margin-bottom:8px;">
                    <span>IVA (21%)</span><span id="payment-iva">0.00€</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; gap:10px;">
                        <button id="btn-add-bag-payment"
                            style="border:1px solid var(--primary); background:#fff; color:var(--primary); padding:5px 12px; border-radius:8px; cursor:pointer; font-weight:700; font-size:0.8rem; display:flex; align-items:center; gap:5px; transition:0.2s;"
                            onclick="toggleBagPayment()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                            + Bossa (0,10€)
                        </button>
                        <button id="btn-discount-payment"
                            style="border:1px solid #10b981; background:#fff; color:#10b981; padding:5px 12px; border-radius:8px; cursor:pointer; font-weight:700; font-size:0.8rem; display:flex; align-items:center; gap:5px; transition:0.2s;"
                            onclick="toggleDiscountPayment()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>
                            -15% Treballador
                        </button>
                    </div>
                    <div style="font-size:1.5rem; font-weight:900; color:var(--text-main);">
                        Total: <span id="payment-total-price">0.00€</span>
                        <div id="payment-discount-applied" style="display:none; color:#10b981; font-size:0.75rem; text-align:right;">Descompte aplicat!</div>
                    </div>
                </div>
            </div>

            {{-- Mètode de pagament --}}
            <p style="font-size:0.7rem; font-weight:800; color:#aaa; text-transform:uppercase; margin:0 0 10px 0;">Mètode de pagament</p>
            <div style="display: flex; gap: 12px; margin-bottom: 18px;">
                <button class="method-btn" id="pm-btn-efectiu" onclick="selectPaymentMethod('Efectiu')">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                    <div style="margin-top:5px;">💵 Efectiu</div>
                </button>
                <button class="method-btn" id="pm-btn-targeta" onclick="selectPaymentMethod('Targeta')">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <div style="margin-top:5px;">💳 Targeta</div>
                </button>
            </div>

            {{-- Secció de canvi (només efectiu) --}}
            <div id="cash-change-section" style="display:none; background:#f0f8ff; border-radius:14px; padding:16px; margin-bottom:18px; border:1px solid #d0e8ff;">
                <label style="font-weight:800; font-size:0.85rem; color:#4e73df; display:block; margin-bottom:8px;">💶 Import entregat pel client:</label>
                <div style="display:flex; gap:10px; align-items:center; margin-bottom:12px;">
                    <input type="number" id="cash-given" step="0.01" min="0" placeholder="0.00"
                        style="flex:1; padding:12px 15px; font-size:1.3rem; font-weight:800; border:2px solid #4e73df; border-radius:12px; outline:none; color:#1b2559;"
                        oninput="calculateChange()">
                    <span style="font-size:1.5rem; font-weight:900; color:#1b2559;">€</span>
                </div>
                <div id="change-display" style="display:none; background:#fff; border-radius:10px; padding:12px 16px; border:2px solid #22c55e;">
                    <div style="font-size:0.8rem; color:#888; font-weight:600; margin-bottom:4px;">CANVI A RETORNAR</div>
                    <div id="change-amount" style="font-size:2rem; font-weight:900; color:#16a34a;">0.00€</div>
                </div>
                <div id="change-insufficient" style="display:none; background:#fff0f0; border-radius:10px; padding:10px 16px; border:1px solid #ff4d4d; color:#cc0000; font-weight:700; font-size:0.85rem;">
                    ⚠️ Import insuficient per cobrir el total.
                </div>
            </div>

            {{-- Accions --}}
            <button id="btn-confirm-payment" onclick="confirmPayment()"
                style="display:none; width:100%; padding:18px; background:var(--primary); color:#fff; border:none; border-radius:15px; font-weight:900; font-size:1.05rem; cursor:pointer; box-shadow:0 8px 20px rgba(78,115,223,0.3); transition:0.2s; margin-bottom:10px;">
                ✅ Confirmar i Tancar Venda
            </button>
            <button style="border:none; background:none; color:#aaa; width:100%; cursor:pointer; font-weight:700; font-size:0.9rem; padding:8px;"
                onclick="closePaymentMethodModal()">⬅ Tornar</button>
        </div>
    </div>

    <div id="aggregated-products-modal"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(8px); z-index:3000; justify-content:center; align-items:center;">
        <div style="background:#fff; border-radius:24px; width:450px; max-height:85vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.2);">
            <div style="padding:20px 30px; border-bottom:1px solid #e9edf7; display:flex; align-items:center; justify-content:space-between; background:#f4f7fe; flex-shrink:0;">
                <h2 style="margin:0; font-size:1.3rem;">📦 Sumatori de Productes</h2>
                <button onclick="closeAggregatedProductsModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#aaa; transition:0.2s;" onmouseover="this.style.color='#000'" onmouseout="this.style.color='#aaa'">&times;</button>
            </div>
            <div id="aggregated-list" style="flex:1; overflow-y:auto; padding:20px 30px;">
                <!-- Aquí s'injecten -->
            </div>
        </div>
    </div>

    {{-- ===== PÀGINA COMPLETA D'ENCÀRRECS ===== --}}
    <div id="pending-preorders-modal"
        style="display:none; position:fixed; inset:0; background:#f4f7fe; z-index:2000; flex-direction:column; overflow:hidden;">

        <div style="background:#fff; padding:20px 35px; border-bottom:1px solid #e9edf7; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="background:var(--primary); color:#fff; width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </div>
                <div>
                    <h1 style="margin:0; font-size:1.4rem; color:var(--text-main);">Encàrrecs Pendents</h1>
                    <p style="margin:0; color:#aaa; font-size:0.8rem;" id="preorders-page-subtitle">Carregant...</p>
                </div>
            </div>
            <div style="display:flex; gap:10px;">
                <button onclick="openAggregatedProductsModal()"
                    style="background:#fff; border:2px solid var(--primary); padding:10px 20px; border-radius:12px; cursor:pointer; font-weight:800; color:var(--primary); font-size:0.9rem; display:flex; align-items:center; gap:8px; transition:0.2s;"
                    onmouseover="this.style.background='var(--primary)'; this.style.color='#fff';" onmouseout="this.style.background='#fff'; this.style.color='var(--primary)';">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    Productes encarregats
                </button>
                <button onclick="closePendingPreordersPage()"
                    style="background:#f4f7fe; border:none; padding:12px 22px; border-radius:12px; cursor:pointer; font-weight:800; color:var(--text-main); font-size:0.9rem; display:flex; align-items:center; gap:8px; transition:0.2s;"
                    onmouseover="this.style.background='#e9edf7'" onmouseout="this.style.background='#f4f7fe'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Tornar al TPV
                </button>
            </div>
        </div>

        <div style="flex:1; overflow-y:auto; padding:30px 35px;">
            <div id="preorders-list" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap:20px;"></div>
            <div id="preorders-empty" style="display:none; text-align:center; padding:80px 20px; color:#aaa;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity:0.3; margin-bottom:20px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                <p style="font-size:1.2rem; font-weight:700;">Cap encàrrec pendent</p>
                <p style="font-size:0.9rem;">Tots els encàrrecs s'han cobrat o annúl·lat.</p>
            </div>
        </div>
    </div>

    {{-- ===== MODAL COBRAR ENCÀRREC (Rich) ===== --}}
    <div id="charge-preorder-modal"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); backdrop-filter:blur(8px); z-index:3000; justify-content:center; align-items:center;">
        <div style="background:#fff; border-radius:24px; width:540px; max-height:92vh; overflow-y:auto; padding:35px; box-shadow:0 25px 60px rgba(0,0,0,0.2);">

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                <h2 style="margin:0; font-size:1.4rem;">💳 Cobrar Encàrrec</h2>
                <span id="charge-order-badge" style="background:#f4f7fe; color:var(--text-main); font-weight:900; padding:6px 14px; border-radius:20px; font-size:0.9rem;"></span>
            </div>

            <input type="hidden" id="charging-preorder-id">
            <input type="hidden" id="charging-worker-id">

            {{-- Resum productes encàrrec --}}
            <div style="background:#f8f9fe; border-radius:16px; padding:16px; margin-bottom:18px;">
                <p style="font-size:0.7rem; font-weight:800; color:#aaa; text-transform:uppercase; margin:0 0 12px 0;">Productes de l'encàrrec</p>
                <div id="charge-order-summary" style="display:flex; flex-direction:column; gap:6px;"></div>
                <div style="border-top:1px dashed #dde; margin:12px 0 8px;"></div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <button id="btn-add-bag-charge"
                        style="border:1px solid var(--primary); background:#fff; color:var(--primary); padding:5px 12px; border-radius:8px; cursor:pointer; font-weight:700; font-size:0.8rem; display:flex; align-items:center; gap:5px; transition:0.2s;"
                        onclick="toggleBagCharge()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                        + Bossa (0,10€)
                    </button>
                    <div style="font-size:1.5rem; font-weight:900; color:var(--text-main);">Total: <span id="charge-total-price">0.00€</span></div>
                </div>
            </div>

            {{-- Selecció treballador --}}
            <p style="font-size:0.7rem; font-weight:800; color:#aaa; text-transform:uppercase; margin:0 0 10px 0;">1. Qui cobra l'encàrrec?</p>
            <div id="charge-worker-list" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px;">
                @foreach($workers as $worker)
                    <button class="worker-pill-btn" onclick="selectChargeWorker({{ $worker->id }}, this)">
                        {{ $worker->name }}
                    </button>
                @endforeach
            </div>

            {{-- Mètode --}}
            <p style="font-size:0.7rem; font-weight:800; color:#aaa; text-transform:uppercase; margin:0 0 10px 0;">2. Mètode de pagament</p>
            <div style="display:flex; gap:12px; margin-bottom:18px;">
                <button class="method-btn" id="charge-btn-efectiu" onclick="selectChargeMethod('Efectiu')">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                    <div style="margin-top:5px;">💵 Efectiu</div>
                </button>
                <button class="method-btn" id="charge-btn-targeta" onclick="selectChargeMethod('Targeta')">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    <div style="margin-top:5px;">💳 Targeta</div>
                </button>
            </div>

            {{-- Canvi efectiu --}}
            <div id="charge-cash-section" style="display:none; background:#f0f8ff; border-radius:14px; padding:16px; margin-bottom:18px; border:1px solid #d0e8ff;">
                <label style="font-weight:800; font-size:0.85rem; color:#4e73df; display:block; margin-bottom:8px;">💶 Import entregat pel client:</label>
                <div style="display:flex; gap:10px; align-items:center; margin-bottom:12px;">
                    <input type="number" id="charge-cash-given" step="0.01" min="0" placeholder="0.00"
                        style="flex:1; padding:12px 15px; font-size:1.3rem; font-weight:800; border:2px solid #4e73df; border-radius:12px; outline:none; color:#1b2559;"
                        oninput="calculateChargeChange()">
                    <span style="font-size:1.5rem; font-weight:900; color:#1b2559;">€</span>
                </div>
                <div id="charge-change-display" style="display:none; background:#fff; border-radius:10px; padding:12px 16px; border:2px solid #22c55e;">
                    <div style="font-size:0.8rem; color:#888; font-weight:600; margin-bottom:4px;">CANVI A RETORNAR</div>
                    <div id="charge-change-amount" style="font-size:2rem; font-weight:900; color:#16a34a;">0.00€</div>
                </div>
                <div id="charge-change-insufficient" style="display:none; background:#fff0f0; border-radius:10px; padding:10px 16px; border:1px solid #ff4d4d; color:#cc0000; font-weight:700; font-size:0.85rem;">
                    ⚠️ Import insuficient per cobrir el total.
                </div>
            </div>

            {{-- Confirmar --}}
            <button id="btn-confirm-charge" onclick="confirmChargePreorder()"
                style="display:none; width:100%; padding:18px; background:var(--primary); color:#fff; border:none; border-radius:15px; font-weight:900; font-size:1.05rem; cursor:pointer; box-shadow:0 8px 20px rgba(78,115,223,0.3); transition:0.2s; margin-bottom:10px;">
                ✅ Confirmar i Tancar Encàrrec
            </button>
            <button style="border:none; background:none; color:#aaa; width:100%; cursor:pointer; font-weight:700; font-size:0.9rem; padding:8px;"
                onclick="closeChargeModal()">&#8592; Cancel·lar</button>
        </div>
    </div>

    {{-- ===== MODAL RECUPERAR TICKET APARCAT ===== --}}
    <div id="parked-tickets-modal"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:2500; justify-content:center; align-items:center;">
        <div class="modal-content"
            style="background:#fff; width:450px; padding:30px; border-radius:15px; text-align:left; max-height:80vh; overflow-y:auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="margin:0;">⏸️ Tickets Aparcats</h2>
                <button onclick="document.getElementById('parked-tickets-modal').style.display='none'" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#aaa;">&times;</button>
            </div>
            <div id="parked-tickets-list" style="display:flex; flex-direction:column; gap:10px;"></div>
        </div>
    </div>

    <script>
        const TICKET_CONFIG = @json(config('ticket'));

        let selectedPaymentMethod = 'Efectiu';
        let cart = [];
        let selectedWorkerId = null;
        let isCreatingPreorder = false;

        function setPaymentMethod(method) {
            selectedPaymentMethod = method;
            document.getElementById('btn-cash').classList.remove('active');
            document.getElementById('btn-card').classList.remove('active');
            if (method === 'Efectiu') {
                document.getElementById('btn-cash').classList.add('active');
            } else {
                document.getElementById('btn-card').classList.add('active');
            }
        }

        function changeQtyFromCard(id, delta) {
            const card = document.getElementById(`prod-card-${id}`);
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const img = card.dataset.img;

            if (delta === 1 && (name.toLowerCase().includes('poll') || name.toLowerCase().includes('pollo'))) {
                openChickenModal(id, name, price, img);
                return;
            }

            if (delta === -1) {
                const existingItems = cart.filter(i => i.id === id);
                if (existingItems.length > 0) {
                    const lastExisting = existingItems[existingItems.length - 1];
                    updateQuantity(id, -1, name, price, img, lastExisting.notes);
                }
                return;
            }

            updateQuantity(id, delta, name, price, img, '');
        }

        function openChickenModal(id, name, price, img) {
            document.getElementById('chicken-modal-id').value = id;
            document.getElementById('chicken-modal-name').value = name;
            document.getElementById('chicken-modal-price').value = price;
            document.getElementById('chicken-modal-img').value = img;
            document.getElementById('chicken-modal-title').innerText = "Opcions: " + name;
            document.querySelector('input[name="chicken_suc"][value="Sense suc"]').checked = true;
            document.querySelector('input[name="chicken_cucci"][value="Normal"]').checked = true;
            updateChickenOptionsUI();
            document.getElementById('chicken-modal').style.display = 'flex';
        }

        function closeChickenModal() { document.getElementById('chicken-modal').style.display = 'none'; }

        function updateChickenOptionsUI() {
            document.querySelectorAll('input[name="chicken_suc"]').forEach(radio => {
                radio.parentElement.classList.toggle('selected', radio.checked);
            });
            document.querySelectorAll('input[name="chicken_cucci"]').forEach(radio => {
                radio.parentElement.classList.toggle('selected', radio.checked);
            });
        }

        function addChickenToCart() {
            const id = parseInt(document.getElementById('chicken-modal-id').value);
            const name = document.getElementById('chicken-modal-name').value;
            const price = parseFloat(document.getElementById('chicken-modal-price').value);
            const img = document.getElementById('chicken-modal-img').value;

            const suc = document.querySelector('input[name="chicken_suc"]:checked').value;
            const cucci = document.querySelector('input[name="chicken_cucci"]:checked').value;

            let notes = [suc];
            if (cucci !== 'Normal') notes.push(cucci);
            notes = notes.join(', ');

            updateQuantity(id, 1, name, price, img, notes);
            closeChickenModal();
        }

        function updateQuantity(id, change, name = '', price = 0, img = '', notes = '') {
            if (change > 0) {
                let targetCard = document.getElementById(`prod-card-${id}`);
                let deductMultiplier = 1;

                if (name === '1/2 Pollastre (Pit i cuixa)') {
                    const pollastreCard = Array.from(document.querySelectorAll('.product-card')).find(c => c.dataset.name === 'Pollastre');
                    if (pollastreCard) {
                        targetCard = pollastreCard;
                        deductMultiplier = 0.5;
                    }
                }

                if (targetCard) {
                    const stockStr = targetCard.dataset.stock;
                    if (stockStr !== 'null') {
                        const stockLimit = parseFloat(stockStr);

                        let currentTotal = 0;
                        cart.forEach(i => {
                            if (i.id === parseInt(targetCard.dataset.id)) currentTotal += i.quantity;
                            if (i.name === '1/2 Pollastre (Pit i cuixa)' && targetCard.dataset.name === 'Pollastre') {
                                currentTotal += i.quantity * 0.5;
                            }
                        });

                        if (currentTotal + (change * deductMultiplier) > stockLimit) {
                            alert(`No queda més estoc! S'assoliria el límit de ${stockLimit} unitats de ${targetCard.dataset.name}.`);
                            return;
                        }
                    }
                }
            }

            const cartKey = id + '|' + notes;
            const existing = cart.find(i => i.cartKey === cartKey);

            if (existing) {
                existing.quantity += change;
                if (existing.quantity <= 0) {
                    cart = cart.filter(i => i.cartKey !== cartKey);
                }
            } else if (change > 0) {
                cart.push({ id, name, price, img, quantity: 1, notes, cartKey });
            }
            renderCart();
        }

        function removeFromCart(cartKey) {
            cart = cart.filter(i => i.cartKey !== cartKey);
            renderCart();
        }

        function clearCart(confirmPrompt = false) {
            if (confirmPrompt && cart.length > 0) {
                if (!confirm("Estàs segur que vols buidar tota la comanda actual?")) return;
            }
            cart = [];
            renderCart();
        }

        function renderCart() {
            const list = document.getElementById('cart-list');
            let total = 0;
            const ivaPercent = 0.21;
            list.innerHTML = '';

            document.querySelectorAll('.qty-display').forEach(el => el.innerText = '0');

            cart.forEach((item) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                const cardQty = document.getElementById(`card-qty-${item.id}`);
                if (cardQty) cardQty.innerText = parseInt(cardQty.innerText || 0) + item.quantity;

                let notesHtml = '';
                if (item.notes) {
                    notesHtml = `<div style="font-size: 0.75rem; color: #e67e22; font-weight: 700; background: #fff3e0; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 4px;">${item.notes}</div>`;
                }

                list.innerHTML += `
                <div class="cart-item">
                    <div class="cart-item-header">
                        <img src="${item.img}">
                        <div class="item-info">
                            <h4>${item.name}</h4>
                            <p>${item.price.toFixed(2)}€ / unitat</p>
                            ${notesHtml}
                        </div>
                        <button class="btn-remove" onclick="removeFromCart('${item.cartKey}')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                    <div class="qty-controls">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button class="btn-qty" onclick="updateQuantity(${item.id}, -1, '', 0, '', '${item.notes}')">-</button>
                            <span style="font-weight: 800; min-width: 20px; text-align: center;">${item.quantity}</span>
                            <button class="btn-qty" onclick="updateQuantity(${item.id}, 1, '', 0, '', '${item.notes}')">+</button>
                        </div>
                        <div style="font-weight: 800;">${itemTotal.toFixed(2)}€</div>
                    </div>
                </div>
            `;
            });

            const baseImposable = total / (1 + ivaPercent);
            const importIva = total - baseImposable;

            document.getElementById('sub-total').innerText = baseImposable.toFixed(2) + '€';
            document.getElementById('iva').innerText = importIva.toFixed(2) + '€';
            document.getElementById('cart-total').innerText = total.toFixed(2) + '€';

            const clearBtn = document.getElementById('btn-clear-cart');
            if(clearBtn) {
                clearBtn.style.display = cart.length > 0 ? 'flex' : 'none';
            }
        }

        function filterCategory(cat, btn) {
            document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.product-card').forEach(card => {
                card.style.display = (cat === 'all' || card.dataset.categories.includes(cat)) ? 'block' : 'none';
            });
        }

        // Variables per preservar les dades quan editem un encàrrec existent
        let _editPreorderTime = null;
        let _editPreorderName = null;

        function openUserModal(asPreorder) {
            if (cart.length === 0) { alert("La comanda està buida!"); return; }
            isCreatingPreorder = asPreorder;
            document.getElementById('preorder-fields').style.display = asPreorder ? 'block' : 'none';
            document.getElementById('btn-final-confirm').innerText = asPreorder ? 'GUARDAR ENCÀRREC' : 'OBRIR COBRAMENT';
            document.getElementById('user-modal').style.display = 'flex';
            selectedWorkerId = null;
            document.querySelectorAll('.worker-btn').forEach(btn => {
                btn.style.background = '#fbfbfb';
                btn.style.color = '#000';
            });
            document.getElementById('btn-final-confirm').style.display = 'none';

            if (asPreorder) {
                // Si venim d'editar un encàrrec, restaurem la seva hora i nom originals
                if (_editPreorderTime !== null) {
                    document.getElementById('preorder-time').value = _editPreorderTime;
                    document.getElementById('preorder-name').value = _editPreorderName || '';
                    _editPreorderTime = null;
                    _editPreorderName = null;
                } else {
                    let d = new Date(); d.setMinutes(d.getMinutes() + 15);
                    let hh = String(d.getHours()).padStart(2, '0');
                    let mm = String(d.getMinutes()).padStart(2, '0');
                    document.getElementById('preorder-time').value = `${hh}:${mm}`;
                    document.getElementById('preorder-name').value = '';
                }
            }
        }

        function selectWorker(id, clickedBtn) {
            selectedWorkerId = id;
            document.querySelectorAll('.worker-btn').forEach(btn => {
                btn.style.background = '#fbfbfb';
                btn.style.color = '#000';
            });
            clickedBtn.style.background = 'var(--primary)';
            clickedBtn.style.color = '#fff';
            document.getElementById('btn-final-confirm').style.display = 'inline-block';
        }

        function closeUserModal() { document.getElementById('user-modal').style.display = 'none'; }

        let isBagAddedPayment = false;
        let isDiscountAppliedPayment = false;
        let currentPaymentTotal = 0;
        let finalPaymentTotalCached = 0; // Guardem el final calculat
        let selectedPaymentMethodFinal = null;

        function toggleBagPayment() {
            isBagAddedPayment = !isBagAddedPayment;
            let btn = document.getElementById('btn-add-bag-payment');
            if (isBagAddedPayment) {
                btn.style.background = 'var(--primary)';
                btn.style.color = '#fff';
            } else {
                btn.style.background = '#fff';
                btn.style.color = 'var(--primary)';
            }
            updatePaymentTotalUI();
            calculateChange();
        }

        function toggleDiscountPayment() {
            isDiscountAppliedPayment = !isDiscountAppliedPayment;
            let btn = document.getElementById('btn-discount-payment');
            let msj = document.getElementById('payment-discount-applied');
            if (isDiscountAppliedPayment) {
                btn.style.background = '#10b981';
                btn.style.color = '#fff';
                msj.style.display = 'block';
            } else {
                btn.style.background = '#fff';
                btn.style.color = '#10b981';
                msj.style.display = 'none';
            }
            updatePaymentTotalUI();
            calculateChange();
        }

        function updatePaymentTotalUI() {
            let baseTotal = currentPaymentTotal;
            if (isDiscountAppliedPayment) {
                baseTotal = baseTotal * 0.85; // Apliquem 15% de descompte als productes
            }
            let finalTotal = baseTotal + (isBagAddedPayment ? 0.10 : 0); // La bossa no té descompte
            finalPaymentTotalCached = finalTotal;

            document.getElementById('payment-total-price').innerText = finalTotal.toFixed(2) + '€';
            const base = finalTotal / 1.21;
            const iva = finalTotal - base;
            document.getElementById('payment-base').innerText = base.toFixed(2) + '€';
            document.getElementById('payment-iva').innerText = iva.toFixed(2) + '€';
        }

        function renderPaymentOrderSummary() {
            const container = document.getElementById('payment-order-summary');
            container.innerHTML = '';
            cart.forEach(item => {
                const lineTotal = (item.price * item.quantity).toFixed(2);
                let noteTxt = item.notes ? `<span style="color:#e67e22; font-size:0.75rem;"> (${item.notes})</span>` : '';
                container.innerHTML += `
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.88rem;">
                        <span style="color:#555;">${item.quantity}× <strong>${item.name}</strong>${noteTxt}</span>
                        <span style="font-weight:700; color:#1b2559;">${lineTotal}€</span>
                    </div>`;
            });
        }

        function selectPaymentMethod(method) {
            selectedPaymentMethodFinal = method;
            document.getElementById('pm-btn-efectiu').classList.toggle('active', method === 'Efectiu');
            document.getElementById('pm-btn-targeta').classList.toggle('active', method === 'Targeta');

            const cashSection = document.getElementById('cash-change-section');
            if (method === 'Efectiu') {
                cashSection.style.display = 'block';
                document.getElementById('cash-given').value = '';
                document.getElementById('change-display').style.display = 'none';
                document.getElementById('change-insufficient').style.display = 'none';
                setTimeout(() => document.getElementById('cash-given').focus(), 100);
            } else {
                cashSection.style.display = 'none';
            }
            document.getElementById('btn-confirm-payment').style.display = 'block';
        }

        function calculateChange() {
            if (selectedPaymentMethodFinal !== 'Efectiu') return;
            const given = parseFloat(document.getElementById('cash-given').value) || 0;
            const total = finalPaymentTotalCached;
            const changeDisplay = document.getElementById('change-display');
            const insufficient = document.getElementById('change-insufficient');
            const confirmBtn = document.getElementById('btn-confirm-payment');

            if (given <= 0) {
                changeDisplay.style.display = 'none';
                insufficient.style.display = 'none';
                confirmBtn.style.display = 'block';
                return;
            }
            if (given < total) {
                changeDisplay.style.display = 'none';
                insufficient.style.display = 'block';
                confirmBtn.style.display = 'none';
            } else {
                const change = given - total;
                document.getElementById('change-amount').innerText = change.toFixed(2) + '€';
                changeDisplay.style.display = 'block';
                insufficient.style.display = 'none';
                confirmBtn.style.display = 'block';
            }
        }

        function confirmPayment() {
            if (!selectedPaymentMethodFinal) {
                alert('Selecciona primer el mètode de pagament.');
                return;
            }
            processCheckout(selectedPaymentMethodFinal);
        }

        function openPaymentMethodModal() {
            // Sempre calculem el total correctament des del carret
            currentPaymentTotal = 0;
            cart.forEach(item => currentPaymentTotal += item.price * item.quantity);

            if (isCreatingPreorder) {
                processCheckout('Pendent'); // Skipeja el pagament si és encàrrec
                return;
            }

            isBagAddedPayment = false;
            isDiscountAppliedPayment = false;
            selectedPaymentMethodFinal = null;

            let bagBtn = document.getElementById('btn-add-bag-payment');
            if (bagBtn) { bagBtn.style.background = '#fff'; bagBtn.style.color = 'var(--primary)'; }

            let discBtn = document.getElementById('btn-discount-payment');
            if (discBtn) { discBtn.style.background = '#fff'; discBtn.style.color = '#10b981'; }
            document.getElementById('payment-discount-applied').style.display = 'none';

            document.getElementById('pm-btn-efectiu').classList.remove('active');
            document.getElementById('pm-btn-targeta').classList.remove('active');
            document.getElementById('cash-change-section').style.display = 'none';
            document.getElementById('btn-confirm-payment').style.display = 'none';
            document.getElementById('change-display').style.display = 'none';
            document.getElementById('change-insufficient').style.display = 'none';

            updatePaymentTotalUI();
            renderPaymentOrderSummary();

            document.getElementById('user-modal').style.display = 'none';
            document.getElementById('payment-modal').style.display = 'flex';
        }

        function closePaymentMethodModal() {
            document.getElementById('payment-modal').style.display = 'none';
        }

        function escapeTicketHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatTicketQty(value) {
            return Number(value || 0).toLocaleString('ca-ES', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            });
        }

        function formatTicketMoney(value) {
            return Number(value || 0).toLocaleString('ca-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function getTicketDataLabel(rawDate) {
            const parsedDate = rawDate ? new Date(rawDate) : new Date();
            if (Number.isNaN(parsedDate.getTime())) {
                return '';
            }
            return parsedDate.toLocaleDateString('ca-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        }

        function ticketIvaRateForProduct(productName, cfg) {
            const name = String(productName || '').toLowerCase();
            const keywords = cfg.general_iva_keywords || [];
            for (let i = 0; i < keywords.length; i++) {
                if (name.includes(String(keywords[i]).toLowerCase())) {
                    return Number(cfg.iva_general) || 21;
                }
            }
            return Number(cfg.iva_reduced) || 10;
        }

        function paymentTicketLabel(method) {
            const m = String(method || '').trim();
            if (m === 'Efectiu') return 'EFECTIU';
            if (m === 'Targeta') return 'TARGETA';
            return m.toUpperCase() || 'EFECTIU';
        }

        function buildTicketHtml(order, paymentMethodLabel, receiptExtras) {
            const cfg = TICKET_CONFIG || {};
            const extras = receiptExtras || {};
            const total = Number(order.total_price || 0);
            const items = Array.isArray(order.items) ? order.items : [];
            const dataLabel = getTicketDataLabel(order.created_at);
            const method = paymentMethodLabel || order.payment_method || 'Efectiu';
            const payUpper = paymentTicketLabel(method);

            const series = String(cfg.invoice_series || 'QS');
            const padLen = Math.max(4, parseInt(cfg.invoice_number_pad, 10) || 8);
            const invoiceFull = order.fiscal_full_number
                || `${series}${String(order.id).padStart(padLen, '0')}`;

            const paperW = Math.min(96, Math.max(58, parseInt(cfg.paper_width_mm, 10) || 80));

            const ivaBuckets = {};

            let itemsRowsHtml = '';
            items.forEach(item => {
                const qty = Number(item.quantity || 0);
                const price = Number(item.price_at_sale || 0);
                const lineTotal = qty * price;
                const rawName = item.product && item.product.name ? item.product.name : 'Producte';
                const note = item.notes ? ` (${String(item.notes)})` : '';
                const desc = escapeTicketHtml((rawName + note).toUpperCase());
                const rate = ticketIvaRateForProduct(rawName, cfg);
                const baseLine = lineTotal / (1 + rate / 100);
                const ivaLine = lineTotal - baseLine;
                if (!ivaBuckets[rate]) {
                    ivaBuckets[rate] = { base: 0, iva: 0 };
                }
                ivaBuckets[rate].base += baseLine;
                ivaBuckets[rate].iva += ivaLine;

                itemsRowsHtml += '<tr>'
                    + '<td class="num">' + formatTicketQty(qty) + '</td>'
                    + '<td class="desc">' + desc + '</td>'
                    + '<td class="num">' + formatTicketMoney(price) + '</td>'
                    + '<td class="num">' + formatTicketMoney(lineTotal) + '</td>'
                    + '</tr>';
            });

            const ivaRates = Object.keys(ivaBuckets).map(r => parseFloat(r, 10)).sort((a, b) => a - b);
            let ivaRowsHtml = '';
            ivaRates.forEach(rate => {
                const b = ivaBuckets[rate];
                ivaRowsHtml += '<tr>'
                    + '<td class="num">' + formatTicketMoney(b.base) + '</td>'
                    + '<td class="num">' + formatTicketMoney(rate) + ' %</td>'
                    + '<td class="num">' + formatTicketMoney(b.iva) + '</td>'
                    + '</tr>';
            });

            const cashGiven = extras.cashGiven != null ? Number(extras.cashGiven) : null;
            const changeAmt = extras.changeAmount != null ? Number(extras.changeAmount) : null;
            const showCash = method === 'Efectiu' && cashGiven != null && !Number.isNaN(cashGiven) && cashGiven > 0;

            let cashBlockHtml = '';
            if (showCash) {
                cashBlockHtml += '<div class="twocol"><span>LLIURAT</span><span>' + formatTicketMoney(cashGiven) + '</span></div>';
                cashBlockHtml += '<div class="twocol"><span>CANVI</span><span>' + formatTicketMoney(changeAmt != null && !Number.isNaN(changeAmt) ? changeAmt : 0) + '</span></div>';
            }
            cashBlockHtml += '<div class="twocol payline"><span>' + escapeTicketHtml(payUpper) + '</span><span>' + formatTicketMoney(total) + '</span></div>';

            const biz = escapeTicketHtml(cfg.business_name || 'LA CRESTA');
            const addr1 = escapeTicketHtml(cfg.address_line1 || '');
            const city = escapeTicketHtml(cfg.postal_city || '');
            const phone = escapeTicketHtml(cfg.phone || '');
            const nif = escapeTicketHtml(cfg.nif_line || '');

            return '<!doctype html><html><head><meta charset="utf-8">'
                + '<title>Ticket ' + order.id + '</title>'
                + '<style>'
                + '@page { margin: 4mm; }'
                + 'body{font-family:"Courier New",Courier,monospace;font-size:11px;width:' + paperW + 'mm;max-width:' + paperW + 'mm;margin:0 auto;padding:2mm 1mm;color:#000;}'
                + '.center{text-align:center;}'
                + '.biz{font-weight:700;font-size:12px;margin-bottom:2px;}'
                + '.hdrline{margin:1px 0;}'
                + 'hr{border:none;border-top:1px solid #000;margin:6px 0;}'
                + '.twocol{display:flex;justify-content:space-between;gap:6px;margin:3px 0;}'
                + '.twocol span:last-child{text-align:right;white-space:nowrap;}'
                + '.totalrow{font-weight:700;font-size:12px;margin-top:4px;}'
                + '.payline{margin-top:4px;}'
                + 'table.items{width:100%;border-collapse:collapse;margin:4px 0;font-size:10px;}'
                + 'table.items th{text-align:left;font-weight:700;border-bottom:1px solid #000;padding:2px 0;}'
                + 'table.items th.num{text-align:right;}'
                + 'table.items td{padding:2px 0;vertical-align:top;}'
                + 'table.items td.num{text-align:right;white-space:nowrap;}'
                + 'table.items td.desc{word-break:break-word;padding-right:4px;}'
                + 'table.iva{width:100%;border-collapse:collapse;margin:4px 0;font-size:10px;}'
                + 'table.iva th{text-align:left;font-weight:700;border-bottom:1px solid #000;padding:2px 0;}'
                + 'table.iva th.num,table.iva td.num{text-align:right;}'
                + '.metahead{display:flex;justify-content:space-between;gap:8px;margin:4px 0;font-size:10px;}'
                + '.footer{margin-top:8px;text-align:center;font-size:10px;}'
                + '</style></head><body>'
                + '<div class="center"><div class="biz">' + biz + '</div></div>'
                + '<div class="center hdrline">' + addr1 + '</div>'
                + '<div class="center hdrline">' + city + '</div>'
                + '<div class="center hdrline">Tel: ' + phone + '</div>'
                + '<div class="center hdrline">NIF: ' + nif + '</div>'
                + '<hr>'
                + '<div class="metahead"><span>FACTURA: ' + escapeTicketHtml(invoiceFull) + '</span><span>DATA: ' + escapeTicketHtml(dataLabel) + '</span></div>'
                + '<hr>'
                + '<table class="items"><thead><tr>'
                + '<th>UNIT.</th><th>DESCRIPCIÓ</th><th class="num">PREU</th><th class="num">IMPORT</th>'
                + '</tr></thead><tbody>' + itemsRowsHtml + '</tbody></table>'
                + '<hr>'
                + '<div class="twocol totalrow"><span>TOTAL</span><span>' + formatTicketMoney(total) + '</span></div>'
                + cashBlockHtml
                + '<hr>'
                + '<table class="iva"><thead><tr>'
                + '<th>BASE</th><th class="num">% IVA</th><th class="num">TOTAL IVA</th>'
                + '</tr></thead><tbody>' + ivaRowsHtml + '</tbody></table>'
                + '<hr>'
                + '<div class="footer">GRÀCIES PER LA SEVA VISITA</div>'
                + '<script>'
                + 'window.onload=function(){window.focus();window.print();setTimeout(function(){window.close();},200);};'
                + '<\/script>'
                + '</body></html>';
        }

        async function printTicketByOrderId(orderId, paymentMethodLabel, receiptExtras) {
            if (!orderId) return;

            try {
                const response = await fetch(`/orders/${orderId}/details`);
                if (!response.ok) {
                    throw new Error('No s han pogut obtenir les dades del ticket');
                }

                const data = await response.json();
                if (!data || !data.order) {
                    throw new Error('Resposta invalida del ticket');
                }

                const printWindow = window.open('', '_blank', 'width=520,height=820');
                if (!printWindow) {
                    alert("No s'ha pogut obrir la finestra d'impressio. Comprova el bloquejador de pop-ups.");
                    return;
                }

                printWindow.document.open();
                printWindow.document.write(buildTicketHtml(data.order, paymentMethodLabel, receiptExtras));
                printWindow.document.close();
            } catch (error) {
                console.error(error);
                alert("La venda s'ha registrat, pero el ticket no s'ha pogut imprimir.");
            }
        }

        function processCheckout(paymentMethod) {
            if (!selectedWorkerId) return;

            let total = finalPaymentTotalCached; // Enviem el total ja calculat amb descompte i bossa

            // NOTA: isCreatingPreorder en teoria skipeja this modal, 
            // però per si de cas no apliquem discount a pendents (preorders)
            if (isCreatingPreorder) {
                total = currentPaymentTotal;
            }

            const data = {
                worker_id: selectedWorkerId,
                total_price: total,
                payment_method: paymentMethod,
                cart: cart,
                is_preorder: isCreatingPreorder
            };

            if (isCreatingPreorder) {
                data.pickup_time = document.getElementById('preorder-time').value;
                data.customer_name = document.getElementById('preorder-name').value;
            }

            fetch('/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
                .then(res => {
                    if (!res.ok) return res.json().then(err => { throw err; });
                    return res.json();
                })
                .then(async data => {
                    alert(data.message);

                    if (!isCreatingPreorder && data.order_id) {
                        let receiptExtras = {};
                        if (paymentMethod === 'Efectiu') {
                            const givenRaw = document.getElementById('cash-given')?.value;
                            const given = parseFloat(givenRaw) || 0;
                            if (given > 0) {
                                receiptExtras.cashGiven = given;
                                receiptExtras.changeAmount = Math.max(0, given - total);
                            }
                        }
                        await printTicketByOrderId(data.order_id, paymentMethod, receiptExtras);
                    }

                    // Actualitzem l'estoc visualment al DOM (sense recarregar la pàgina)
                    cart.forEach(item => {
                        let targetCard = document.getElementById(`prod-card-${item.id}`);
                        let deductQty = item.quantity;

                        if (item.name === '1/2 Pollastre (Pit i cuixa)') {
                            const pollastreCard = Array.from(document.querySelectorAll('.product-card')).find(c => c.dataset.name === 'Pollastre');
                            if (pollastreCard) {
                                targetCard = pollastreCard;
                                deductQty = item.quantity * 0.5;
                            }
                        }

                        if (targetCard && targetCard.dataset.stock !== 'null') {
                            let currentStock = parseFloat(targetCard.dataset.stock);
                            let newStock = currentStock - deductQty;
                            targetCard.dataset.stock = newStock;

                            let badge = targetCard.querySelector('.stock-badge');
                            if (badge) {
                                if (newStock <= 0) {
                                    badge.classList.add('stock-out');
                                    badge.innerHTML = '⛔ Esgotat';
                                } else {
                                    badge.classList.remove('stock-out');
                                    badge.innerHTML = newStock + ' restants';
                                }
                            }
                        }
                    });

                    cart = [];
                    renderCart();
                    closeUserModal();
                    closePaymentMethodModal();
                    fetchPendingPreorders(); // Refresh encàrrecs
                })
                .catch(err => {
                    console.error(err);
                    alert("Error durant la venda");
                });
        }

        // --- LÒGICA D'ENCÀRRECS / CUES ---
        window.currentPendingOrders = [];
        
        function fetchPendingPreorders() {
            fetch('/orders/pending')
                .then(res => res.json())
                .then(data => {
                    window.currentPendingOrders = data.orders || [];
                    const list = document.getElementById('preorders-list');
                    const emptyState = document.getElementById('preorders-empty');
                    list.innerHTML = '';
                    let urgentCount = 0;

                    const now = new Date();
                    const currentTotalMins = now.getHours() * 60 + now.getMinutes();

                    if (data.orders.length === 0) {
                        emptyState.style.display = 'block';
                        document.getElementById('preorders-page-subtitle').innerText = 'Cap encàrrec pendent';
                    } else {
                        emptyState.style.display = 'none';
                        document.getElementById('preorders-page-subtitle').innerText = `${data.orders.length} enc${data.orders.length === 1 ? 'àrrec' : 'àrrecs'} pendent${data.orders.length === 1 ? '' : 's'}`;
                    }

                    data.orders.forEach(order => {
                        let isUrgent = false;
                        let diffMins = null;
                        let timeBadgeHtml = '';

                        if (order.pickup_time) {
                            const [h, m] = order.pickup_time.split(':');
                            const orderMins = parseInt(h) * 60 + parseInt(m);
                            diffMins = orderMins - currentTotalMins;

                            if (diffMins <= 15 && diffMins >= -120) {
                                urgentCount++;
                                isUrgent = true;
                            }
                        }

                        const cardBorder = isUrgent ? '2px solid #ff4d4d' : '1px solid #e9edf7';
                        const cardBg = isUrgent ? '#fff8f8' : '#fff';
                        const urgentBadge = isUrgent
                            ? `<span style="background:#ff4d4d; color:#fff; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:800; letter-spacing:0.5px;">⚡ URGENT</span>`
                            : '';

                        const timeLabel = order.pickup_time
                            ? `🕒 ${order.pickup_time}`
                            : '🕒 Sense hora';

                        let itemsHtml = order.items.map(i => {
                            let noteTxt = i.notes ? `<span style="color:#e67e22; font-size:0.8rem;"> (${i.notes})</span>` : '';
                            return `<div style="padding:3px 0; font-size:0.85rem; color:#555;">
                                <span style="font-weight:700; color:#4e73df;">${i.quantity}×</span> ${i.product.name}${noteTxt}
                            </div>`;
                        }).join('');

                        const customer = order.customer_name
                            ? `<span style="font-size:0.85rem; color:#666; font-weight:600;">👤 ${order.customer_name}</span>`
                            : '';

                        // Serialize items per passar al modal
                        const itemsJson = JSON.stringify(order.items).replace(/'/g, "\\'").replace(/"/g, '&quot;');

                        list.innerHTML += `
                            <div style="background:${cardBg}; border:${cardBorder}; border-radius:18px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,0.04); display:flex; flex-direction:column; gap:14px; transition:0.2s;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                    <div>
                                        <div style="font-weight:900; font-size:1.15rem; color:var(--text-main); margin-bottom:4px;">
                                            Encàrrec #${order.pickup_number} ${urgentBadge}
                                        </div>
                                        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                                            <span style="font-size:0.82rem; color:#888; font-weight:700;">${timeLabel}</span>
                                            ${customer}
                                        </div>
                                    </div>
                                    <div style="font-size:1.4rem; font-weight:900; color:var(--text-main);">${parseFloat(order.total_price).toFixed(2)}€</div>
                                </div>
                                <div style="background:#f8f9fe; border-radius:10px; padding:10px 14px;">${itemsHtml}</div>
                                <div style="display:flex; gap:8px;">
                                    <button style="flex:1; padding:10px; font-size:0.82rem; font-weight:700; border:1.5px solid #ff4d4d; background:none; color:#ff4d4d; border-radius:10px; cursor:pointer; transition:0.2s;"
                                        onmouseover="this.style.background='#fff0f0'" onmouseout="this.style.background='none'"
                                        onclick="deletePreorder(${order.id})">🗑 Anul·lar</button>
                                    <button style="flex:1; padding:10px; font-size:0.82rem; font-weight:700; border:1.5px solid var(--primary); background:none; color:var(--primary); border-radius:10px; cursor:pointer; transition:0.2s;"
                                        onmouseover="this.style.background='#f0f3ff'" onmouseout="this.style.background='none'"
                                        onclick="editPreorder(${order.id})">✏️ Modificar</button>
                                    <button style="flex:2; padding:10px; font-size:0.9rem; font-weight:800; background:var(--primary); color:#fff; border:none; border-radius:10px; cursor:pointer; box-shadow:0 4px 10px rgba(78,115,223,0.3); transition:0.2s;"
                                        onmouseover="this.style.background='#3d5fc4'" onmouseout="this.style.background='var(--primary)'"
                                        onclick='openChargePreorderModal(${order.id}, ${order.total_price}, ${JSON.stringify(order.items)}, ${order.pickup_number})'>💳 Cobrar</button>
                                </div>
                            </div>
                        `;
                    });

                    const badge = document.getElementById('preorder-badge');
                    if (urgentCount > 0) {
                        badge.style.display = 'inline-block';
                        badge.innerText = urgentCount;
                    } else {
                        badge.style.display = 'none';
                    }
                });
        }

        function openPendingPreordersModal() {
            fetchPendingPreorders();
            document.getElementById('pending-preorders-modal').style.display = 'flex';
        }

        function closePendingPreordersPage() {
            document.getElementById('pending-preorders-modal').style.display = 'none';
        }

        function openAggregatedProductsModal() {
            const list = document.getElementById('aggregated-list');
            list.innerHTML = '';
            
            let productSummary = {};
            
            if (window.currentPendingOrders) {
                window.currentPendingOrders.forEach(order => {
                    order.items.forEach(item => {
                        let name = item.product.name;
                        // Opcionalment pots agrupar per "producte + nota" o només per "producte"
                        // Aquí agrupem per nom per mantenir-ho senzill i global.
                        if (!productSummary[name]) {
                            productSummary[name] = {
                                img: item.product.image_path,
                                qty: 0
                            };
                        }
                        productSummary[name].qty += parseFloat(item.quantity);
                    });
                });
            }
            
            let sortedProducts = Object.keys(productSummary).sort((a,b) => productSummary[b].qty - productSummary[a].qty);
            
            if (sortedProducts.length === 0) {
                list.innerHTML = '<p style="text-align:center; color:#888;">Cap producte encarregat.</p>';
            } else {
                sortedProducts.forEach(name => {
                    let info = productSummary[name];
                    let imgString = info.img ? `<img src="/${info.img}" style="width:50px; height:50px; object-fit:cover; border-radius:10px;">` : `<div style="width:50px;height:50px;background:#eee;border-radius:10px;"></div>`;
                    list.innerHTML += `
                        <div style="display:flex; align-items:center; gap:15px; border-bottom:1px solid #f0f0f0; padding-bottom:15px; margin-bottom:15px;">
                            ${imgString}
                            <div style="flex:1;">
                                <div style="font-weight:800; color:var(--text-main);">${name}</div>
                                <div style="font-size:0.85rem; color:#888;">Encàrrecs pendents</div>
                            </div>
                            <div style="font-size:1.4rem; font-weight:900; background:var(--primary); color:white; padding:8px 16px; border-radius:12px;">
                                ${info.qty}
                            </div>
                        </div>
                    `;
                });
            }
            
            document.getElementById('aggregated-products-modal').style.display = 'flex';
        }

        function closeAggregatedProductsModal() {
            document.getElementById('aggregated-products-modal').style.display = 'none';
        }

        // --- LÒGICA DE TICKETS APARCATS ---
        let parkedTickets = JSON.parse(localStorage.getItem('quickserve_parked_tickets')) || [];

        function updateParkedBadge() {
            const badge = document.getElementById('parked-badge');
            if (parkedTickets.length > 0) {
                badge.style.display = 'inline-block';
                badge.innerText = parkedTickets.length;
            } else {
                badge.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateParkedBadge();
        });

        function parkCurrentTicket() {
            if (cart.length === 0) {
                alert("No hi ha cap producte a l'ordre actual per aparcar.");
                return;
            }
            const currentTotal = parseFloat(document.getElementById('cart-total').innerText);
            const parkedOrder = {
                timestamp: Date.now(),
                total: currentTotal,
                items: JSON.parse(JSON.stringify(cart))
            };
            
            parkedTickets.push(parkedOrder);
            localStorage.setItem('quickserve_parked_tickets', JSON.stringify(parkedTickets));
            updateParkedBadge();

            // Netejar la comanda actual sense modificar l'estoc
            cart = [];
            renderCart();
        }

        function openParkedTicketsModal() {
            const list = document.getElementById('parked-tickets-list');
            list.innerHTML = '';

            if (parkedTickets.length === 0) {
                list.innerHTML = `<div style="text-align:center; padding:30px; color:#aaa;">No hi ha cap ticket aparcat.</div>`;
            } else {
                parkedTickets.forEach((ticket, index) => {
                    const date = new Date(ticket.timestamp);
                    const timeStr = date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0');
                    const itemsPreview = ticket.items.map(i => `${i.quantity}x ${i.name}`).join(', ');

                    list.innerHTML += `
                        <div style="border:1px solid #ddd; padding:15px; border-radius:10px; margin-bottom:10px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                                <div style="flex:1;">
                                    <div style="font-weight:900; margin-bottom:4px;">Aparcat a les ${timeStr}</div>
                                    <div style="font-size:0.75rem; color:#888; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
                                        ${itemsPreview}
                                    </div>
                                </div>
                                <div style="font-weight:900; font-size:1.1rem; color:var(--text-main); margin-left:15px; text-align:right;">
                                    ${ticket.total.toFixed(2)}€
                                </div>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <button onclick="restoreParkedTicket(${index})" style="background:var(--primary); color:#fff; border:none; padding:8px 16px; border-radius:8px; font-weight:bold; cursor:pointer; font-size:0.8rem; box-shadow:0 4px 10px rgba(78,115,223,0.2); transition:transform 0.1s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                                    ✅ Recuperar
                                </button>
                                <button onclick="deleteParkedTicket(${index})" title="Eliminar" style="background:var(--danger); color:#fff; border:none; padding:8px 16px; border-radius:8px; font-weight:bold; cursor:pointer; font-size:0.8rem; box-shadow:0 4px 10px rgba(239,68,68,0.2); transition:transform 0.1s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                                    🗑️ Eliminar
                                </button>
                            </div>
                        </div>
                    `;
                });
            }

            document.getElementById('parked-tickets-modal').style.display = 'flex';
        }

        function restoreParkedTicket(index) {
            if (cart.length > 0) {
                if (!confirm("Atenció: Tens una venda en curs. Si recuperes el ticket, l'ordre actual es sobreescriurà. Vols continuar?")) {
                    return;
                }
            }

            cart = parkedTickets[index].items;
            deleteParkedTicket(index); // L'esborra de la llista d'aparcats
            renderCart();
            document.getElementById('parked-tickets-modal').style.display = 'none';
        }

        function deleteParkedTicket(index) {
            parkedTickets.splice(index, 1);
            localStorage.setItem('quickserve_parked_tickets', JSON.stringify(parkedTickets));
            updateParkedBadge();
            openParkedTicketsModal(); // Refresca el modal si estava obert
        }

        let isBagAddedCharge = false;
        let currentChargeTotal = 0;
        let selectedChargeMethod = null;
        let currentChargeItems = [];

        function toggleBagCharge() {
            isBagAddedCharge = !isBagAddedCharge;
            let btn = document.getElementById('btn-add-bag-charge');
            if (isBagAddedCharge) {
                btn.style.background = 'var(--primary)';
                btn.style.color = '#fff';
            } else {
                btn.style.background = '#fff';
                btn.style.color = 'var(--primary)';
            }
            updateChargeTotalUI();
            calculateChargeChange();
        }

        function updateChargeTotalUI() {
            let finalTotal = parseFloat(currentChargeTotal) + (isBagAddedCharge ? 0.10 : 0);
            document.getElementById('charge-total-price').innerText = finalTotal.toFixed(2) + '€';
        }

        function openChargePreorderModal(id, total, items, pickupNum) {
            // Amagar la pàgina d'encàrrecs (queda al darrere)
            document.getElementById('charging-preorder-id').value = id;
            document.getElementById('charging-worker-id').value = '';
            document.querySelectorAll('#charge-worker-list .worker-pill-btn').forEach(b => b.classList.remove('active'));

            currentChargeTotal = parseFloat(total);
            currentChargeItems = items || [];
            isBagAddedCharge = false;
            selectedChargeMethod = null;

            // Reset bag btn
            let bagBtn = document.getElementById('btn-add-bag-charge');
            if (bagBtn) { bagBtn.style.background = '#fff'; bagBtn.style.color = 'var(--primary)'; }

            // Reset mètodes
            document.getElementById('charge-btn-efectiu').classList.remove('active');
            document.getElementById('charge-btn-targeta').classList.remove('active');
            document.getElementById('charge-cash-section').style.display = 'none';
            document.getElementById('btn-confirm-charge').style.display = 'none';
            document.getElementById('charge-change-display').style.display = 'none';
            document.getElementById('charge-change-insufficient').style.display = 'none';

            updateChargeTotalUI();

            // Resum productes
            const summaryContainer = document.getElementById('charge-order-summary');
            summaryContainer.innerHTML = '';
            currentChargeItems.forEach(item => {
                const lineTotal = (parseFloat(item.price_at_sale) * item.quantity).toFixed(2);
                let noteTxt = item.notes ? `<span style="color:#e67e22; font-size:0.75rem;"> (${item.notes})</span>` : '';
                summaryContainer.innerHTML += `
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.88rem;">
                        <span style="color:#555;">${item.quantity}× <strong>${item.product.name}</strong>${noteTxt}</span>
                        <span style="font-weight:700; color:#1b2559;">${lineTotal}€</span>
                    </div>`;
            });

            // Badge encàrrec
            document.getElementById('charge-order-badge').innerText = pickupNum ? `Encàrrec #${pickupNum}` : `ID #${id}`;

            document.getElementById('charge-preorder-modal').style.display = 'flex';
        }

        function selectChargeMethod(method) {
            selectedChargeMethod = method;
            document.getElementById('charge-btn-efectiu').classList.toggle('active', method === 'Efectiu');
            document.getElementById('charge-btn-targeta').classList.toggle('active', method === 'Targeta');

            const cashSection = document.getElementById('charge-cash-section');
            if (method === 'Efectiu') {
                cashSection.style.display = 'block';
                document.getElementById('charge-cash-given').value = '';
                document.getElementById('charge-change-display').style.display = 'none';
                document.getElementById('charge-change-insufficient').style.display = 'none';
                setTimeout(() => document.getElementById('charge-cash-given').focus(), 100);
            } else {
                cashSection.style.display = 'none';
            }
            document.getElementById('btn-confirm-charge').style.display = 'block';
        }

        function calculateChargeChange() {
            if (selectedChargeMethod !== 'Efectiu') return;
            const given = parseFloat(document.getElementById('charge-cash-given').value) || 0;
            const total = parseFloat(currentChargeTotal) + (isBagAddedCharge ? 0.10 : 0);
            const changeDisplay = document.getElementById('charge-change-display');
            const insufficient = document.getElementById('charge-change-insufficient');
            const confirmBtn = document.getElementById('btn-confirm-charge');

            if (given <= 0) {
                changeDisplay.style.display = 'none';
                insufficient.style.display = 'none';
                confirmBtn.style.display = 'block';
                return;
            }
            if (given < total) {
                changeDisplay.style.display = 'none';
                insufficient.style.display = 'block';
                confirmBtn.style.display = 'none';
            } else {
                const change = given - total;
                document.getElementById('charge-change-amount').innerText = change.toFixed(2) + '€';
                changeDisplay.style.display = 'block';
                insufficient.style.display = 'none';
                confirmBtn.style.display = 'block';
            }
        }

        function selectChargeWorker(workerId, btn) {
            document.getElementById('charging-worker-id').value = workerId;
            document.querySelectorAll('#charge-worker-list .worker-pill-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        function closeChargeModal() {
            document.getElementById('charge-preorder-modal').style.display = 'none';
        }

        function confirmChargePreorder() {
            const workerId = document.getElementById('charging-worker-id').value;
            if (!workerId) {
                alert('Selecciona primer qui cobra l\'encàrrec.');
                return;
            }
            if (!selectedChargeMethod) {
                alert('Selecciona el mètode de pagament.');
                return;
            }
            executeChargePreorder(selectedChargeMethod);
        }

        function executeChargePreorder(paymentMethod) {
            const id = document.getElementById('charging-preorder-id').value;
            const workerId = document.getElementById('charging-worker-id').value;

            let totalToCharge = parseFloat(currentChargeTotal) + (isBagAddedCharge ? 0.10 : 0);

            fetch(`/orders/${id}/charge`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    payment_method: paymentMethod,
                    worker_id: workerId,
                    add_bag: isBagAddedCharge
                })
            }).then(r => r.json()).then(async res => {
                if (res.success) {
                    let receiptExtras = {};
                    if (paymentMethod === 'Efectiu') {
                        const given = parseFloat(document.getElementById('charge-cash-given').value) || 0;
                        if (given > 0) {
                            receiptExtras.cashGiven = given;
                            receiptExtras.changeAmount = Math.max(0, given - totalToCharge);
                        }
                    }
                    await printTicketByOrderId(id, paymentMethod, receiptExtras);
                    document.getElementById('charge-preorder-modal').style.display = 'none';
                    fetchPendingPreorders();
                } else {
                    alert('Error al cobrar l\'encàrrec.');
                }
            });
        }

        // Refresh regular of preorders
        setInterval(fetchPendingPreorders, 30000);
        fetchPendingPreorders();

        function deletePreorder(id) {
            if (!confirm('Segur que vols anul·lar aquest encàrrec? L\'estoc dels aliments es restaurarà.')) return;
            fetch(`/orders/${id}/cancel`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            }).then(() => {
                fetchPendingPreorders();
            });
        }

        function editPreorder(id) {
            fetch(`/orders/${id}/details`)
                .then(r => r.json())
                .then(data => {
                    // Recuperar els items
                    cart = data.order.items.map(item => ({
                        id: item.product_id,
                        name: item.product.name,
                        price: parseFloat(item.price_at_sale),
                        quantity: item.quantity,
                        img: '/' + item.product.image_path,
                        notes: item.notes || '',
                        cartKey: item.product_id + '|' + (item.notes || '')
                    }));

                    // Guardem la hora i el nom en variables globals perquè
                    // openUserModal no les sobreescrigui quan l'usuari cliqui "Encàrrec"
                    _editPreorderTime = data.order.pickup_time || '';
                    _editPreorderName = data.order.customer_name || '';

                    // Ara ho cancelem al backend per restaurar l'estoc temporalment
                    fetch(`/orders/${id}/cancel`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    }).then(() => {
                        document.getElementById('pending-preorders-modal').style.display = 'none';
                        renderCart();
                    });
                });
        }

        function adjustTime(amount) {
            const input = document.getElementById('preorder-time');
            let d = new Date();

            if (amount === 0) {
                let totalMins = d.getHours() * 60 + d.getMinutes();
                totalMins = Math.round(totalMins / 15) * 15;
                d.setHours(Math.floor(totalMins / 60));
                d.setMinutes(totalMins % 60);
                input.value = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
                return;
            }

            let isEmpty = !input.value;

            if (!isEmpty) {
                let parts = input.value.split(':');
                d.setHours(parseInt(parts[0], 10));
                d.setMinutes(parseInt(parts[1], 10));
                d.setSeconds(0);
                d.setMilliseconds(0);
            }

            if (isEmpty) {
                let totalMins = d.getHours() * 60 + d.getMinutes();
                if (amount > 0) {
                    totalMins = Math.ceil((totalMins + 1) / amount) * amount;
                } else {
                    let absAmt = Math.abs(amount);
                    totalMins = Math.floor((totalMins - 1) / absAmt) * absAmt;
                }
                d.setHours(Math.floor(totalMins / 60));
                d.setMinutes(totalMins % 60);
            } else {
                d.setMinutes(d.getMinutes() + amount);
                let totalMins = d.getHours() * 60 + d.getMinutes();
                totalMins = Math.round(totalMins / 15) * 15;
                d.setHours(Math.floor(totalMins / 60));
                d.setMinutes(totalMins % 60);
            }

            input.value = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        }

        // --- PIN ADMIN ---
        function openAdminPinModal() {
            let input = document.getElementById('admin-pin-input');
            input.value = '';
            document.getElementById('admin-pin-modal').style.display = 'flex';
            setTimeout(() => input.focus(), 100);
        }

        function verifyAdminPin() {
            const pin = document.getElementById('admin-pin-input').value;
            if (!pin || pin.length !== 4) {
                alert('El PIN ha de tenir 4 dígits.');
                return;
            }

            fetch('/admin/verify-pin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ pin: pin })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '/admin';
                    } else {
                        alert(data.error || 'PIN incorrecte.');
                        document.getElementById('admin-pin-input').value = '';
                        document.getElementById('admin-pin-input').focus();
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Error validant el PIN");
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            let pinInput = document.getElementById('admin-pin-input');
            if (pinInput) {
                pinInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        verifyAdminPin();
                    }
                });
            }
        });

    </script>
</body>

</html>