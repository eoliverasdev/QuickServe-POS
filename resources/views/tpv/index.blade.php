<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TPV - La Cresta</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* --- Estils del Menú Lateral (Clar) --- */
        #side-menu {
            transition: transform 0.3s ease-in-out;
            box-shadow: 5px 0 20px rgba(0,0,0,0.1);
        }
        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            color: #555;
            text-decoration: none;
            border-radius: 12px;
            transition: 0.2s;
            margin-bottom: 8px;
        }
        .menu-link:hover, .menu-link.active {
            background: rgba(255, 237, 5, 0.2);
            color: #000;
            font-weight: bold;
        }
        .menu-link svg { width: 20px; height: 20px; }

        /* --- Estils Generals TPV (Fons Blanc) --- */
        body { margin: 0; background-color: #f8f9fa; color: #333; font-family: 'Segoe UI', sans-serif; }
        .tpv-container { display: flex; gap: 20px; padding: 20px; height: 95vh; }
        
        .main-section { flex: 2; display: flex; flex-direction: column; gap: 15px; padding-left: 60px; }

        /* Barra de Categories */
        .categories-bar { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px; }
        .btn-category { 
            padding: 10px 22px; background: #fff; border: 1px solid #ddd; border-radius: 25px; 
            cursor: pointer; font-weight: bold; color: #666; white-space: nowrap; transition: all 0.2s;
        }
        .btn-category.active { background: #ffed05; color: #000; border-color: #ffed05; box-shadow: 0 4px 10px rgba(255,237,5,0.3); }

        /* Graella de Productes */
        .products-grid { 
            display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); 
            gap: 15px; overflow-y: auto; padding-right: 10px;
        }
        .product-card {
            padding: 12px; background: #fff; border: 1px solid #eee; border-radius: 16px;
            cursor: pointer; text-align: center; transition: all 0.2s; display: flex; flex-direction: column;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .product-card:hover { border-color: #ffed05; transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.1); }
        .product-img { width: 100%; height: 110px; object-fit: cover; border-radius: 10px; margin-bottom: 8px; background: #f0f0f0; }
        .product-card strong { color: #333; font-size: 0.9rem; }
        .product-card span { color: #28a745; font-size: 1.1rem; margin-top: 5px; font-weight: bold; }

        /* Tiquet lateral */
        .sidebar { 
            flex: 1; background: #fff; padding: 20px; border-radius: 20px; 
            border: 1px solid #eee; display: flex; flex-direction: column; max-width: 380px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .cart-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f5f5f5; align-items: center; }
        .btn-pay { width: 100%; padding: 18px; background: #ffed05; color: #000; border: none; border-radius: 12px; font-size: 1.1rem; font-weight: 900; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        .btn-pay:hover { background: #e6d500; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,237,5,0.4); }

        /* Modal de Cobrament Corregit */
        #user-modal {
            display: none; 
            position: fixed; 
            inset: 0; 
            background: rgba(0,0,0,0.5); 
            backdrop-filter: blur(4px); 
            z-index: 1000; 
            justify-content: center; 
            align-items: center;
        }
        .modal-content { 
            background: #fff; 
            padding: 30px; 
            border-radius: 24px; 
            width: 90%;
            max-width: 450px; 
            text-align: center; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.2); 
        }
        .worker-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .btn-worker {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 12px;
            background: #f9f9f9;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: 0.2s;
        }
        .btn-worker:hover {
            background: #ffed05;
            border-color: #ffed05;
        }
    </style>
</head>
<body>

<div id="user-modal">
    <div class="modal-content">
        <h2 style="margin: 0 0 10px 0; text-transform: uppercase; font-weight: 900;">Finalitzar <span style="color: #b8a500;">Venda</span></h2>
        <p style="color: #666;">Selecciona qui està atenent:</p>
        
        <div class="worker-grid">
            @foreach($workers as $worker)
                <button class="btn-worker" onclick="handleWorkerClick(this)" data-id="{{ $worker->id }}" data-name="{{ $worker->name }}">
                    {{ $worker->name }}
                </button>
            @endforeach
        </div>

        <button onclick="closeUserModal()" style="background: none; border: none; color: #999; cursor: pointer; text-decoration: underline; font-weight: bold;">CANCEL·LAR</button>
    </div>
</div>

<button onclick="toggleMenu()" style="position: fixed; top: 20px; left: 20px; z-index: 100; background: #ffed05; border: none; padding: 12px; border-radius: 12px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2.5"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
</button>

<aside id="side-menu" style="position: fixed; top: 0; left: 0; height: 100%; width: 280px; background: #fff; z-index: 101; transform: translateX(-100%); padding: 25px 20px; box-sizing: border-box; border-right: 1px solid #eee;">
    <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 35px; position: relative;">
        <button onclick="toggleMenu()" style="position: absolute; right: -5px; top: -10px; background: none; border: none; color: #ccc; cursor: pointer; font-size: 1.5rem;">&times;</button>
        <img src="{{ asset('images/logo-la-cresta-sense-fons.png') }}" alt="Logo La Cresta" style="max-width: 140px; height: auto; margin-bottom: 15px;">
        <div style="height: 2px; width: 40px; background: #ffed05; border-radius: 2px;"></div>
    </div>

    <nav>
        <a href="{{ route('admin.index') }}" class="menu-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Administració
        </a>
        <a href="/" class="menu-link active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            Venda TPV
        </a>
    </nav>

    <div style="position: absolute; bottom: 30px; left: 20px; right: 20px;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="width: 100%; padding: 12px; background: #fdf2f2; color: #e04a4a; border: 1px solid #fee2e2; border-radius: 10px; cursor: pointer; font-weight: 700; text-transform: uppercase; font-size: 0.85rem;">
                Tancar Sessió
            </button>
        </form>
    </div>
</aside>

<div class="tpv-container">
    <div class="main-section">
        <h2 style="margin: 0; color: #333; text-transform: uppercase; letter-spacing: 1px; font-weight: 900;">Terminal <span style="color: #b8a500;">Punt de Venda</span></h2>

        <div class="categories-bar">
            <button onclick="filterCategory('all', this)" class="btn-category active">Tots els Productes</button>
            @foreach($categories as $category)
                <button onclick="filterCategory('{{ $category->name }}', this)" class="btn-category">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        <div class="products-grid">
            @foreach($products as $product)
                <div class="product-card" 
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ $product->price }}"
                    data-categories="{{ $product->categories->pluck('name')->join(' ') }}"
                    onclick="handleProductClick(this)">
                    
                    <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="product-img">
                    <strong>{{ $product->name }}</strong>
                    <span>{{ number_format($product->price, 2) }}€</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="sidebar">
        <h3 style="margin: 0 0 20px 0; border-bottom: 3px solid #ffed05; padding-bottom: 10px; font-size: 1.1rem; color: #333; text-transform: uppercase; letter-spacing: 1px;">Tiquet Actual</h3>
        <ul id="cart-list" style="list-style: none; padding: 0; flex-grow: 1; overflow-y: auto;"></ul>
        
        <div style="margin-top: auto; background: #fbfbfb; padding: 20px; border-radius: 15px; border: 1px solid #eee;">
            <div style="display: flex; justify-content: space-between; font-size: 1.6rem; margin-bottom: 15px;">
                <strong style="color: #999; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Total</strong>
                <span style="color: #333; font-weight: 900;"><span id="cart-total">0.00</span>€</span>
            </div>
            <button onclick="openUserModal()" class="btn-pay">Finalitzar Venda</button>
        </div>
    </div>
</div>

<script>
    function toggleMenu() {
        const menu = document.getElementById('side-menu');
        const isOpen = menu.style.transform === 'translateX(0%)';
        menu.style.transform = isOpen ? 'translateX(-100%)' : 'translateX(0%)';
    }

    let cart = [];

    function handleProductClick(element) {
        const id = parseInt(element.getAttribute('data-id'));
        const name = element.getAttribute('data-name');
        const price = parseFloat(element.getAttribute('data-price'));
        addToCart(id, name, price);
    }

    function addToCart(id, name, price) {
        const existing = cart.find(item => item.id === id);
        if (existing) { existing.quantity++; } 
        else { cart.push({ id, name, price, quantity: 1 }); }
        renderCart();
    }

    function removeFromCart(index) {
        if (cart[index].quantity > 1) { cart[index].quantity--; } 
        else { cart.splice(index, 1); }
        renderCart();
    }

    function renderCart() {
        const list = document.getElementById('cart-list');
        let total = 0;
        list.innerHTML = '';
        cart.forEach((item, index) => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            list.innerHTML += `
                <li class="cart-item">
                    <div style="color: #333"><strong>${item.quantity}x</strong> ${item.name}</div>
                    <div style="color: #28a745; font-weight:bold;">
                        ${subtotal.toFixed(2)}€ 
                        <button onclick="removeFromCart(${index})" style="background:none; border:none; color:#ff4444; font-size:1.4rem; cursor:pointer; margin-left:10px;">&times;</button>
                    </div>
                </li>`;
        });
        document.getElementById('cart-total').innerText = total.toFixed(2);
    }

    function filterCategory(categoryName, button) {
        document.querySelectorAll('.btn-category').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const productCats = card.getAttribute('data-categories');
            card.style.display = (categoryName === 'all' || productCats.includes(categoryName)) ? 'flex' : 'none';
        });
    }

    function openUserModal() {
        if (cart.length === 0) return alert("La cistella està buida!");
        document.getElementById('user-modal').style.display = 'flex';
    }

    function closeUserModal() { 
        document.getElementById('user-modal').style.display = 'none'; 
    }

    function handleWorkerClick(button) {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        processCheckout(id, name);
    }

    function processCheckout(workerId, workerName) {
        if (!confirm(`Confirmar venda cobrada per ${workerName}?`)) return;

        const totalValue = document.getElementById('cart-total').innerText;

        console.log("Dades que enviarem:", {
    workerId: workerId,
    workerName: workerName,
    totalValue: totalValue,
    cart: cart
});
        fetch("{{ route('orders.store') }}", {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                total_price: parseFloat(totalValue),
                payment_method: 'Efectiu',
                worker_id: parseInt(workerId),
                cart: cart
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la resposta del servidor');
            return response.json();
        })
        .then(data => {
            alert("Venda registrada amb èxit!");
            cart = []; 
            renderCart(); 
            closeUserModal();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('No s\'ha pogut registrar la venda. Revisa la connexió.');
        });
    }
</script>

</body>
</html>