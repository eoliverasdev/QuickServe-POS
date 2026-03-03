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

        /* --- Tiquet --- */
        .invoice-sidebar {
            background: #fff;
            padding: 30px;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #e9edf7;
        }

        .invoice-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 25px;
        }

        .cart-items-list {
            flex-grow: 1;
            overflow-y: auto;
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
        }

        .worker-btn:hover { background: var(--primary); color: #fff; }
    </style>
</head>
<body>

<div class="app-wrapper">
    <aside class="navbar-icons">
        <div class="nav-icon active">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        </div>
        <a href="{{ route('admin.index') }}" class="nav-icon">
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
             data-categories="{{ $product->categories->pluck('name')->join(' ') }}">
            
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
                <button type="button" id="btn-cash" class="method-btn active" onclick="setPaymentMethod('Efectiu')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 5px;"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                    Efectiu
                </button>
                <button type="button" id="btn-card" class="method-btn" onclick="setPaymentMethod('Targeta')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 5px;"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    Targeta
                </button>
            </div>

            <button class="btn-place-order" onclick="openUserModal()">Confirmar Comanda</button>
        </div>
    </aside>
</div>

<div id="user-modal">
    <div class="modal-content">
        <h2 style="margin-bottom: 20px;">Qui cobra la venda?</h2>
        @foreach($workers as $worker)
            <button class="worker-btn" onclick="processCheckout('{{ $worker->id }}', '{{ $worker->name }}')">
                {{ $worker->name }}
            </button>
        @endforeach
        <button onclick="closeUserModal()" style="margin-top: 15px; background:none; border:none; color: #999; cursor:pointer;">Cancel·lar</button>
    </div>
</div>

<script>
    let selectedPaymentMethod = 'Efectiu';
    let cart = [];

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
        updateQuantity(id, delta, name, price, img);
    }

    function updateQuantity(id, change, name = '', price = 0, img = '') {
        const existing = cart.find(i => i.id === id);
        if (existing) {
            existing.quantity += change;
            if (existing.quantity <= 0) {
                cart = cart.filter(i => i.id !== id);
            }
        } else if (change > 0) {
            cart.push({ id, name, price: price, img, quantity: 1 });
        }
        renderCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(i => i.id !== id);
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
            if (cardQty) cardQty.innerText = item.quantity;

            list.innerHTML += `
                <div class="cart-item">
                    <div class="cart-item-header">
                        <img src="${item.img}">
                        <div class="item-info">
                            <h4>${item.name}</h4>
                            <p>${item.price.toFixed(2)}€ / unitat</p>
                        </div>
                        <button class="btn-remove" onclick="removeFromCart(${item.id})">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                    <div class="qty-controls">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button class="btn-qty" onclick="updateQuantity(${item.id}, -1)">-</button>
                            <span style="font-weight: 800; min-width: 20px; text-align: center;">${item.quantity}</span>
                            <button class="btn-qty" onclick="updateQuantity(${item.id}, 1)">+</button>
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

    function openUserModal() {
        if (cart.length === 0) return alert("El tiquet està buit");
        document.getElementById('user-modal').style.display = 'flex';
    }

    function closeUserModal() { document.getElementById('user-modal').style.display = 'none'; }

    function processCheckout(workerId, workerName) {
        const totalValue = parseFloat(document.getElementById('cart-total').innerText);
        fetch("{{ route('orders.store') }}", {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                total_price: totalValue,
                payment_method: selectedPaymentMethod,
                worker_id: workerId,
                cart: cart
            })
        })
        .then(res => {
            if (!res.ok) return res.json().then(err => { throw err; });
            return res.json();
        })
        .then(data => {
            alert("Venda realitzada correctament");
            cart = [];
            renderCart();
            closeUserModal();
        })
        .catch(err => {
            console.error(err);
            alert("Error al processar la venda.");
        });
    }
</script>
</body>
</html>