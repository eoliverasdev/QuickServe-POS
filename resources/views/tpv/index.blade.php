<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TPV Premium - La Cresta</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    
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
        }

        .nav-icon.active, .nav-icon:hover {
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
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
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
            box-shadow: 0 10px 20px rgba(0,0,0,0.02);
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
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
            margin-bottom: 25px;
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

        .item-info { flex-grow: 1; }
        .item-info h4 { margin: 0; font-size: 0.9rem; }
        .item-info p { margin: 0; color: var(--text-secondary); font-size: 0.8rem; }

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

        .btn-qty:hover { background: var(--primary); color: #fff; }

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
        }

        /* --- Modal --- */
        #user-modal {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3);
            backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: center;
        }

        .modal-content {
            background: #fff; padding: 40px; border-radius: 30px; width: 400px; text-align: center;
        }

        .worker-btn {
            width: 100%; padding: 15px; margin-bottom: 10px; border: 1px solid #eee;
            border-radius: 15px; background: #fbfbfb; cursor: pointer; font-weight: 600;
            color: #000;
        }

        .worker-btn:hover { background: var(--primary); color: #fff; }

        .option-label {
            flex: 1; padding: 12px; border: 2px solid #eee; border-radius: 12px; cursor: pointer; text-align: center; font-weight: bold; font-size: 0.9rem;
            transition: 0.2s; background: #fff; color: #666;
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
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stock-out {
            background: rgba(255, 77, 77, 0.9);
        }
        .trending-badge {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: linear-gradient(135deg, #ff6b35, #ff9a00);
            color: #fff;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            z-index: 10;
            box-shadow: 0 3px 8px rgba(255,107,53,0.4);
            animation: pulseGlow 2s infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 3px 8px rgba(255,107,53,0.4); }
            50% { box-shadow: 0 3px 16px rgba(255,107,53,0.7); }
        }
    </style>
</head>
<body>

<div class="app-wrapper">
    <aside class="navbar-icons">
        <div class="nav-icon active">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        </div>
        <button class="nav-icon" onclick="openPendingPreordersModal()" style="border:none; cursor:pointer; position:relative; background:none;">
            <!-- Icona capsa encàrrecs -->
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            <span id="preorder-badge" style="display:none; position:absolute; top:-5px; right:-5px; background:var(--danger); color:white; border-radius:50%; padding:2px 5px; font-weight:bold; font-size:10px;">0</span>
        </button>
        <a href="{{ route('admin.index') }}" class="nav-icon" title="Admin">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20v-6M9 20v-10M15 20v-4M3 20h18"></path></svg>
        </a>
        <div style="margin-top: auto;">
             <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-icon" style="background:none; border:none; color: var(--danger);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"></path></svg>
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
        <div class="product-card" 
             id="prod-card-{{ $product->id }}"
             data-id="{{ $product->id }}"
             data-name="{{ $product->name }}"
             data-price="{{ $product->price }}"
             data-img="{{ asset($product->image_path) }}"
             data-stock="{{ $product->stock ?? 'null' }}"
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

            {{-- Badge de Trending (top del dia) --}}
            @if(isset($topAvui) && $topAvui->has($product->id))
                <div class="trending-badge" title="Top {{ $topAvui->keys()->search($product->id) + 1 }} avui!">🔥 Trending</div>
            @endif
            
            <img src="{{ asset($product->image_path) }}" 
                 class="product-img" 
                 onclick="changeQtyFromCard({{ $product->id }}, 1)" 
                 style="cursor: pointer;">

            <h4 style="margin: 0 0 5px 0; cursor: pointer;" 
                onclick="changeQtyFromCard({{ $product->id }}, 1)">
                {{ $product->name }}
            </h4>

            <div class="price-tag">{{ number_format($product->price, 2) }}€</div>
            
            <div class="card-controls">
                <button class="btn-card-qty" onclick="changeQtyFromCard({{ $product->id }}, -1)">-</button>
                <span class="qty-display" id="card-qty-{{ $product->id }}">0</span>
                <button class="btn-card-qty" style="background: var(--primary); color: #fff;" onclick="changeQtyFromCard({{ $product->id }}, 1)">+</button>
            </div>
        </div>
    @endforeach
</section>
    </main>

    <aside class="invoice-sidebar">
        <h2 class="invoice-title">Current Order</h2>
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
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn-place-order" onclick="openUserModal(false)" style="flex:1; padding:15px; font-size:0.95rem;">💵 Venda</button>
                <button class="btn-place-order" onclick="openUserModal(true)" style="flex:1; padding:15px; font-size:0.95rem; background:none; border:2px solid var(--primary); color:var(--dark);">📝 Encàrrec</button>
            </div>
        </div>
    </aside>
</div>

<div id="chicken-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: #fff; padding: 30px; border-radius: 20px; width: 380px; text-align: left;">
        <h2 style="margin-top: 0; margin-bottom: 20px;" id="chicken-modal-title">Opcions de cocció</h2>
        
        <input type="hidden" id="chicken-modal-id">
        <input type="hidden" id="chicken-modal-name">
        <input type="hidden" id="chicken-modal-price">
        <input type="hidden" id="chicken-modal-img">

        <h4 style="margin-bottom: 12px; color: #666; text-transform: uppercase; font-size: 0.8rem;">Opcions de suc</h4>
        <div style="display: flex; gap: 10px; margin-bottom: 25px;">
            <label class="option-label">
                <input type="radio" name="chicken_suc" value="Amb suc" style="display: none;" onchange="updateChickenOptionsUI()">
                Amb Suc
            </label>
            <label class="option-label selected">
                <input type="radio" name="chicken_suc" value="Sense suc" checked style="display: none;" onchange="updateChickenOptionsUI()">
                Sense Suc
            </label>
        </div>

        <h4 style="margin-bottom: 12px; color: #666; text-transform: uppercase; font-size: 0.8rem;">Punt de cocció</h4>
        <div style="display: flex; gap: 10px; margin-bottom: 25px;">
            <label class="option-label">
                <input type="radio" name="chicken_cucci" value="Normal" checked style="display: none;" onchange="updateChickenOptionsUI()">
                Normal
            </label>
            <label class="option-label">
                <input type="radio" name="chicken_cucci" value="Poc cuit" style="display: none;" onchange="updateChickenOptionsUI()">
                Poc Cuit
            </label>
            <label class="option-label">
                <input type="radio" name="chicken_cucci" value="Molt cuit" style="display: none;" onchange="updateChickenOptionsUI()">
                Molt Cuit
            </label>
        </div>

        <button class="btn-place-order" style="margin-top: 0; padding: 15px;" onclick="addChickenToCart()">Afegir a la comanda</button>
        <button type="button" style="width: 100%; border: none; background: none; color: #999; margin-top: 15px; cursor: pointer; font-weight: 600;" onclick="closeChickenModal()">Cancel·lar</button>
    </div>
</div>

<div id="user-modal">
    <div class="modal-content">
        <h2 style="margin-bottom: 20px;">Qui està gestionant?</h2>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
            @foreach($workers as $worker)
                <button class="worker-btn" onclick="selectWorker({{ $worker->id }}, '{{ $worker->name }}')">{{ $worker->name }}</button>
            @endforeach
        </div>
        
        <div id="preorder-fields" style="display:none; margin-top: 20px; text-align:left;">
            <label style="font-weight:bold; display:block; margin-bottom:5px;">Hora de recollida:</label>
            <input type="time" id="preorder-time" style="width:100%; padding: 10px; border-radius:10px; border:1px solid #ccc; margin-bottom:15px;" required>
            
            <label style="font-weight:bold; display:block; margin-bottom:5px;">Nom del Client (Opcional):</label>
            <input type="text" id="preorder-name" style="width:100%; padding: 10px; border-radius:10px; border:1px solid #ccc;">
        </div>

        <div style="margin-top: 20px; text-align: center;">
            <button class="btn-place-order" id="btn-final-confirm" onclick="openPaymentMethodModal()" style="display:none; text-transform:uppercase;">Continuar</button>
            <br><button onclick="closeUserModal()" style="border:none; background:none; color:#999; margin-top:10px; cursor:pointer;">Cancel·lar</button>
        </div>
    </div>
</div>

<div id="payment-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: center;">
    <div class="modal-content">
        <h2 style="margin-top: 0; margin-bottom: 10px;">Com paguen?</h2>
        <p style="color:#666; margin-bottom: 25px;">Selecciona el mètode de pagament</p>

        <div style="display: flex; gap: 15px; margin-bottom: 20px;">
            <button class="method-btn" onclick="processCheckout('Efectiu')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                <div style="margin-top:5px;">Efectiu</div>
            </button>
            <button class="method-btn" onclick="processCheckout('Targeta')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                <div style="margin-top:5px;">Targeta</div>
            </button>
        </div>

        <button style="border:none; background:none; color:#999; width:100%; cursor:pointer; font-weight:800;" onclick="closePaymentMethodModal()">⬅ Tornar</button>
    </div>
</div>

<div id="pending-preorders-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:2000; justify-content:center; align-items:center;">
    <div class="modal-content" style="background:#fff; width:500px; padding:30px; border-radius:15px; text-align:left; max-height:80vh; overflow-y:auto;">
        <h2>📦 Encàrrecs Pendents</h2>
        <div id="preorders-list" style="margin-top:20px; display:flex; flex-direction:column; gap:10px;"></div>
        <button onclick="document.getElementById('pending-preorders-modal').style.display='none'" style="margin-top:20px; width:100%; padding:15px; background:#f0f0f0; border:none; border-radius:10px; cursor:pointer;">Tancar</button>
    </div>
</div>

<div id="charge-preorder-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:3000; justify-content:center; align-items:center;">
    <div class="modal-content" style="background:#fff; width:450px; padding:30px; border-radius:15px; text-align:center;">
        <h2 style="margin-top:0;">Cobrar Encàrrec</h2>
        <input type="hidden" id="charging-preorder-id">
        <input type="hidden" id="charging-worker-id">

        <p style="text-align: left; font-weight: 800; font-size: 0.8rem; color: #aaa; text-transform: uppercase;">1. Qui cobra l'encàrrec?</p>
        <div id="charge-worker-list" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 25px; justify-content: flex-start;">
            @foreach($workers as $worker)
                <button class="worker-pill-btn" onclick="selectChargeWorker({{ $worker->id }}, this)">
                    {{ $worker->name }}
                </button>
            @endforeach
        </div>

        <p style="text-align: left; font-weight: 800; font-size: 0.8rem; color: #aaa; text-transform: uppercase;">2. Mètode de pagament</p>
        <div style="display: flex; gap: 15px; margin-top: 10px;">
            <button class="method-btn" onclick="executeChargePreorder('Efectiu')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                <div style="margin-top:5px;">Efectiu</div>
            </button>
            <button class="method-btn" onclick="executeChargePreorder('Targeta')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                <div style="margin-top:5px;">Targeta</div>
            </button>
        </div>
        
        <button style="border:none; background:none; color:#999; margin-top:25px; cursor:pointer; font-weight:800; text-transform: uppercase; font-size: 0.75rem;" onclick="document.getElementById('charge-preorder-modal').style.display='none'">⬅ Cancel·lar</button>
    </div>
</div>

<script>
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
    }

    function filterCategory(cat, btn) {
        document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.categories.includes(cat)) ? 'block' : 'none';
        });
    }

    function openUserModal(asPreorder) {
        if (cart.length === 0) { alert("La comanda està buida!"); return; }
        isCreatingPreorder = asPreorder;
        document.getElementById('preorder-fields').style.display = asPreorder ? 'block' : 'none';
        document.getElementById('btn-final-confirm').innerText = asPreorder ? 'GUARDAR ENCÀRREC' : 'OBRIR COBRAMENT';
        document.getElementById('user-modal').style.display = 'flex';
        selectedWorkerId = null;
        document.querySelectorAll('.worker-btn').forEach(btn => btn.style.background = '#fbfbfb'); // Reset background
        document.getElementById('btn-final-confirm').style.display = 'none';
        
        if(asPreorder) {
            let d = new Date(); d.setMinutes(d.getMinutes() + 15);
            let hh = String(d.getHours()).padStart(2, '0');
            let mm = String(d.getMinutes()).padStart(2, '0');
            document.getElementById('preorder-time').value = `${hh}:${mm}`;
            document.getElementById('preorder-name').value = '';
        }
    }

    function selectWorker(id, name) {
        selectedWorkerId = id;
        document.querySelectorAll('.worker-btn').forEach(btn => {
            btn.style.background = btn.innerText === name ? 'var(--primary)' : '#fbfbfb';
            btn.style.color = btn.innerText === name ? '#fff' : '#000';
        });
        document.getElementById('btn-final-confirm').style.display = 'inline-block';
    }

    function closeUserModal() { document.getElementById('user-modal').style.display = 'none'; }

    function openPaymentMethodModal() {
        if(isCreatingPreorder) {
            processCheckout('Pendent'); // Skipeja el pagament si és enviat com encàrrec
            return;
        }
        document.getElementById('user-modal').style.display = 'none';
        document.getElementById('payment-modal').style.display = 'flex';
    }

    function closePaymentMethodModal() {
        document.getElementById('payment-modal').style.display = 'none';
    }

    function processCheckout(paymentMethod) {
        if (!selectedWorkerId) return;
        
        let total = 0;
        cart.forEach(item => total += item.price * item.quantity);

        const data = {
            worker_id: selectedWorkerId,
            total_price: total,
            payment_method: paymentMethod,
            cart: cart,
            is_preorder: isCreatingPreorder
        };

        if(isCreatingPreorder) {
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
        .then(data => {
            alert(data.message);

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
    function fetchPendingPreorders() {
        fetch('/orders/pending')
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('preorders-list');
            list.innerHTML = '';
            let urgentCount = 0;

            const now = new Date();
            const currentTotalMins = now.getHours() * 60 + now.getMinutes();

            data.orders.forEach(order => {
                let badgeHtml = '';
                
                if (order.pickup_time) {
                    const [h, m] = order.pickup_time.split(':');
                    const orderMins = parseInt(h) * 60 + parseInt(m);
                    const diff = orderMins - currentTotalMins;

                    if (diff <= 15 && diff >= -120) {
                        urgentCount++;
                        badgeHtml = '<span style="background:red; color:white; padding:2px 8px; border-radius:10px; font-size:0.75rem;">URGENT</span>';
                    }
                }

                let itemsHtml = order.items.map(i => {
                    let noteTxt = i.notes ? ` <span style="color:#e67e22; font-size:0.8rem; font-weight:700;">(${i.notes})</span>` : '';
                    return `• ${i.quantity}x ${i.product.name}${noteTxt}`;
                }).join('<br>');
                let customer = order.customer_name ? ` - ${order.customer_name}` : '';

                list.innerHTML += `
                    <div style="border:1px solid #ddd; padding:15px; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-weight:900; font-size:1.1rem; margin-bottom:5px;">Encàrrec #${order.pickup_number} ${customer} ${badgeHtml}</div>
                            <div style="color:#666; font-size:0.85rem; margin-bottom:5px;">🕒 Recollida: ${order.pickup_time || 'Sense hora'}</div>
                            <div style="font-size:0.8rem; color:#888;">${itemsHtml}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:900; font-size:1.2rem; margin-bottom:10px;">${order.total_price}€</div>
                            <div style="display:flex; gap:5px; justify-content:flex-end;">
                                <button class="btn-place-order" style="padding:10px; font-size:0.85rem; width:auto; border:2px solid var(--danger); background:none; color:var(--danger);" onclick="deletePreorder(${order.id})">Anul·lar</button>
                                <button class="btn-place-order" style="padding:10px; font-size:0.85rem; width:auto; border:2px solid var(--primary); background:none; color:var(--dark);" onclick="editPreorder(${order.id})">Modificar</button>
                                <button class="btn-place-order" style="padding:10px 15px; font-size:0.85rem; width:auto;" onclick="openChargePreorderModal(${order.id})">Cobrar</button>
                            </div>
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

    function openChargePreorderModal(id) {
        document.getElementById('pending-preorders-modal').style.display = 'none';
        document.getElementById('charging-preorder-id').value = id;
        document.getElementById('charging-worker-id').value = ''; // Reset
        document.querySelectorAll('#charge-worker-list .worker-pill-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('charge-preorder-modal').style.display = 'flex';
    }

    function selectChargeWorker(workerId, btn) {
        document.getElementById('charging-worker-id').value = workerId;
        document.querySelectorAll('#charge-worker-list .worker-pill-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    function executeChargePreorder(paymentMethod) {
        const id = document.getElementById('charging-preorder-id').value;
        const workerId = document.getElementById('charging-worker-id').value;

        if (!workerId) {
            alert('Per favor, selecciona primer qui està cobrant l\'encàrrec.');
            return;
        }

        fetch(`/orders/${id}/charge`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                payment_method: paymentMethod,
                worker_id: workerId
            })
        }).then(r => r.json()).then(res => {
            if (res.success) {
                alert('Encàrrec cobrat amb èxit!');
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
        if(!confirm('Segur que vols anul·lar aquest encàrrec? L\'estoc dels aliments es restaurarà.')) return;
        fetch(`/orders/${id}/cancel`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')}
        }).then(() => {
            fetchPendingPreorders();
        });
    }

    function editPreorder(id) {
        if(!confirm('Això carregarà els aliments de l\'encàrrec al tiquet actual, esborrant el guardat original per permetre\'t fer canvis. Vols continuar?')) return;
        
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
                
                // Emmagatzemar les metadades de l'encàrrec a la UI
                // Així quan modifiqui i doni a Encàrrec tindrà les hores guardades
                document.getElementById('preorder-time').value = data.order.pickup_time || '';
                document.getElementById('preorder-name').value = data.order.customer_name || '';
                
                // Ara ho cancelem al backend per restaurar l'estoc temporalment
                fetch(`/orders/${id}/cancel`, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')}
                }).then(() => {
                    document.getElementById('pending-preorders-modal').style.display = 'none';
                    renderCart();
                    alert("L'encàrrec està llest per modificar-se al TPV. \nCobre'l ara amb el botó normal o fes clic a 'Memoritza Encàrrec' de nou quan hagis acabat!");
                });
            });
    }

</script>
</body>
</html>
```