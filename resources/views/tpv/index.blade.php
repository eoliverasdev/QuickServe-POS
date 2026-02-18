<style>
    /* Estils Generals */
    .tpv-container { display: flex; gap: 20px; font-family: sans-serif; padding: 20px; height: 95vh; background-color: #f4f4f9; }
    .main-section { flex: 2; display: flex; flex-direction: column; gap: 15px; }

    /* Barra de Categories */
    .categories-bar { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px; }
    .btn-category { 
        padding: 10px 20px; background: #ebedef; border: 2px solid #ddd; border-radius: 25px; 
        cursor: pointer; font-weight: bold; white-space: nowrap; transition: all 0.2s;
    }
    .btn-category.active { background: #3b82f6; color: white; border-color: #2563eb; }

    /* Graella de Productes */
    .products-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); 
        gap: 15px; 
        overflow-y: auto; 
        padding-right: 10px;
        /* Millora el rendiment quan hi ha molts elements */
        content-visibility: auto;
    }
    .product-card {
        padding: 12px; background: #ffffff; border: 1px solid #ddd; border-radius: 12px;
        cursor: pointer; text-align: center; transition: all 0.1s; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        user-select: none; display: flex; flex-direction: column; justify-content: space-between;
    }
    .product-card:active { transform: scale(0.95); background: #f0f0f0; }

    /* Estil de les Imatges */
    .product-img {
        width: 100%;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 8px;
        background-color: #f9f9f9;
    }

    .product-card strong { display: block; margin-bottom: 5px; color: #333; font-size: 0.95rem; line-height: 1.2; }
    .product-card span { color: #28a745; font-weight: bold; font-size: 1.1rem; }

    /* Tiquet lateral */
    .sidebar { 
        flex: 1; background: #ffffff; padding: 20px; border-radius: 12px; 
        border: 1px solid #eee; display: flex; flex-direction: column; max-width: 400px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .cart-items { list-style: none; padding: 0; flex-grow: 1; overflow-y: auto; }
    .cart-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    
    .btn-pay { width: 100%; padding: 20px; background: #28a745; color: white; border: none; border-radius: 8px; font-size: 1.2rem; font-weight: bold; cursor: pointer; }
    .btn-remove { color: #dc3545; border: none; background: none; cursor: pointer; font-size: 1.5rem; }

    /* Modal de Cobrament */
    #user-modal {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center;
    }
    .modal-content { background: white; padding: 30px; border-radius: 15px; text-align: center; width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    .user-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px; }
    .btn-user { padding: 15px; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; background: #f8f9fa; font-weight: bold; transition: background 0.2s; }
    .btn-user:hover { background: #e2e6ea; }
</style>

<div class="tpv-container">
    <div class="main-section">
        <h2>La Cresta - TPV</h2>

        <div class="categories-bar">
            <button onclick="filterCategory('all', this)" class="btn-category active">Tots</button>
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
                    
                    {{-- Correcció de la ruta: utilitzem directament la ruta del seeder --}}
                    <img src="{{ asset($product->image_path) }}" 
                         alt="{{ $product->name }}" 
                         class="product-img">

                    <div>
                        <strong>{{ $product->name }}</strong>
                        <span>{{ number_format($product->price, 2) }}€</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="sidebar">
        <h2>Tiquet Actual</h2>
        <ul id="cart-list" class="cart-items"></ul>
        <div style="margin-top: auto;">
            <hr>
            <div style="display: flex; justify-content: space-between; font-size: 1.5rem; margin-bottom: 10px;">
                <strong>Total:</strong>
                <span><span id="cart-total">0.00</span>€</span>
            </div>
            <button onclick="openUserModal()" class="btn-pay">FINALITZAR VENDA</button>
        </div>
    </div>
</div>

<div id="user-modal">
    <div class="modal-content">
        <h3>Qui cobra la comanda?</h3>
        <div class="user-grid">
            @foreach($workers as $worker)
                <button class="btn-user" 
                        data-id="{{ $worker->id }}" 
                        data-name="{{ $worker->name }}"
                        onclick="handleWorkerClick(this)">
                    {{ $worker->name }}
                </button>
            @endforeach
        </div>
        <button onclick="closeUserModal()" style="margin-top: 20px; border: none; background: none; color: #666; cursor: pointer; text-decoration: underline;">Cancel·lar</button>
    </div>
</div>

<script>
    let cart = [];

    // --- Lògica de Productes ---
    function handleProductClick(element) {
        const id = parseInt(element.getAttribute('data-id'));
        const name = element.getAttribute('data-name');
        const price = parseFloat(element.getAttribute('data-price'));
        addToCart(id, name, price);
    }

    function addToCart(id, name, price) {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({ id, name, price, quantity: 1 });
        }
        renderCart();
    }

    function removeFromCart(index) {
        if (cart[index].quantity > 1) {
            cart[index].quantity--;
        } else {
            cart.splice(index, 1);
        }
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
                    <div><strong>${item.quantity}x</strong> ${item.name}</div>
                    <div>${subtotal.toFixed(2)}€ <button class="btn-remove" onclick="removeFromCart(${index})">×</button></div>
                </li>`;
        });
        document.getElementById('cart-total').innerText = total.toFixed(2);
    }

    // --- Lògica de Filtres ---
    function filterCategory(categoryName, button) {
        document.querySelectorAll('.btn-category').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const productCats = card.getAttribute('data-categories');
            card.style.display = (categoryName === 'all' || productCats.includes(categoryName)) ? 'flex' : 'none';
        });
    }

    // --- Lògica de Cobrament (Workers) ---
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

        fetch('/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                total_price: document.getElementById('cart-total').innerText,
                payment_method: 'Efectiu',
                worker_id: workerId,
                cart: cart
            })
        })
        .then(response => response.json())
        .then(data => {
            alert("Venda registrada amb èxit!");
            cart = [];
            renderCart();
            closeUserModal();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error en registrar la venda. Revisa la consola.');
        });
    }
</script>