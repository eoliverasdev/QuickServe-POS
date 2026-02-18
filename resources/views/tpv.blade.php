<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Cresta - TPV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans h-screen overflow-hidden">
    <div class="flex h-full">
        <div class="w-2/3 flex flex-col p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-orange-600">La Cresta: Rostisseria</h1>
                <div id="weather-widget" class="text-sm bg-blue-50 p-2 rounded-lg text-blue-600 border border-blue-100">
                    ☀️ Carregant temps...
                </div>
            </div>

            <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                <button onclick="filterCategory(event, 'Tots')" class="cat-btn px-4 py-2 bg-orange-500 text-white rounded-full shadow-sm font-semibold whitespace-nowrap">Tots</button>
                @foreach($products->pluck('category')->unique() as $cat)
                    <button onclick="filterCategory(event, '{{ $cat }}')" class="cat-btn px-4 py-2 bg-white text-gray-600 rounded-full shadow-sm font-semibold whitespace-nowrap hover:bg-orange-100">{{ $cat }}</button>
                @endforeach
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 overflow-y-auto pr-2 flex-1" id="products-grid">
                @foreach($products as $product)
                    <button onclick="addToCart('{{ $product->id }}', '{{ $product->name }}', '{{ $product->price }}')"
                            data-category="{{ $product->category }}"
                            class="product-card bg-white rounded-2xl shadow-sm overflow-hidden border-b-4 border-orange-500 active:scale-95 transition-all text-left group">
                        
                        <div class="h-32 w-full bg-gray-200 overflow-hidden relative">
                            @if($product->image_path)
                                <img src="{{ asset('images/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400 italic">Sense foto</div>
                            @endif
                            
                            @if($product->is_gluten_free)
                                <span class="absolute top-2 right-2 bg-green-500 text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase shadow-sm">SG</span>
                            @endif
                        </div>

                        <div class="p-4">
                            <span class="block font-bold text-gray-800 text-lg leading-tight h-12 overflow-hidden">{{ $product->name }}</span>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-orange-600 font-black text-xl">{{ number_format($product->price, 2) }} €</span>
                                <div class="bg-gray-100 p-1 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="w-1/3 bg-white shadow-2xl flex flex-col border-l border-gray-200">
            <div class="p-6 bg-gray-800 text-white flex justify-between items-center">
                <h2 class="text-xl font-bold uppercase tracking-wider">Comanda</h2>
                <button onclick="clearCart()" class="text-xs text-gray-400 hover:text-red-400 underline">Buidar</button>
            </div>

            <div id="cart-items" class="flex-1 p-4 overflow-y-auto space-y-3">
                <p class="text-gray-400 text-center mt-10 italic">Selecciona algun producte...</p>
            </div>

            <div class="p-6 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-between text-gray-600 mb-2">
                    <span>Base Imposable:</span>
                    <span id="base-total">0.00 €</span>
                </div>
                <div class="flex justify-between text-gray-600 mb-4">
                    <span>IVA (10%):</span>
                    <span id="iva-total">0.00 €</span>
                </div>
                <div class="flex justify-between text-3xl font-black mb-6 text-gray-800">
                    <span>TOTAL:</span>
                    <span id="final-total">0.00 €</span>
                </div>
                
                <button onclick="generarFactura()" class="w-full bg-orange-500 text-white py-4 rounded-2xl font-bold text-xl shadow-lg hover:bg-orange-600 transform transition-active active:scale-95">
                    GENERAR FACTURA PDF
                </button>
            </div>
        </div>
    </div>

    <script>
        let cart = [];

        function addToCart(id, name, price) {
            let cleanPrice = parseFloat(price.toString().replace(',', '.'));
            if (isNaN(cleanPrice)) cleanPrice = 0;

            cart.push({ id, name, price: cleanPrice });
            updateCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCart();
        }

        function clearCart() {
            cart = [];
            updateCart();
        }

        function updateCart() {
            const container = document.getElementById('cart-items');
            const baseEl = document.getElementById('base-total');
            const ivaEl = document.getElementById('iva-total');
            const finalEl = document.getElementById('final-total');

            if (cart.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-center mt-10 italic">Selecciona algun producte...</p>';
                baseEl.innerText = '0.00 €';
                ivaEl.innerText = '0.00 €';
                finalEl.innerText = '0.00 €';
                return;
            }

            let total = cart.reduce((sum, item) => sum + item.price, 0);
            let base = total / 1.1;
            let iva = total - base;

            container.innerHTML = cart.map((item, index) => `
                <div class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm border border-gray-100 animate-fade-in-right">
                    <div>
                        <p class="font-bold text-gray-800">${item.name}</p>
                        <p class="text-xs text-orange-600">${item.price.toFixed(2)} €</p>
                    </div>
                    <button onclick="removeFromCart(${index})" class="text-gray-300 hover:text-red-500 p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            `).join('');

            baseEl.innerText = base.toFixed(2) + ' €';
            ivaEl.innerText = iva.toFixed(2) + ' €';
            finalEl.innerText = total.toFixed(2) + ' €';
        }

        function filterCategory(e, category) {
            document.querySelectorAll('.cat-btn').forEach(btn => {
                btn.classList.remove('bg-orange-500', 'text-white');
                btn.classList.add('bg-white', 'text-gray-600');
            });
            // Fem servir currentTarget per seguretat
            e.currentTarget.classList.add('bg-orange-500', 'text-white');

            document.querySelectorAll('.product-card').forEach(card => {
                if (category === 'Tots' || card.getAttribute('data-category') === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // NOVA FUNCIÓ PER ENVIAR LES DADES AL BACKEND I REBRE EL PDF
        async function generarFactura() {
            if (cart.length === 0) {
                alert("El carretó està buit!");
                return;
            }

            const total = cart.reduce((sum, item) => sum + item.price, 0);

            try {
                const response = await fetch('{{ route("factura.generar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        items: cart,
                        total: total
                    })
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    // Obre el PDF en una pestanya nova
                    window.open(url, '_blank');
                    clearCart();
                } else {
                    alert("Error en generar la factura al servidor.");
                }
            } catch (error) {
                console.error("Error:", error);
                alert("No s'ha pogut connectar amb el servidor.");
            }
        }
    </script>

    <style>
        @keyframes fade-in-right {
            from { opacity: 0; transform: translateX(10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-fade-in-right { animation: fade-in-right 0.2s ease-out; }
    </style>
</body>
</html>