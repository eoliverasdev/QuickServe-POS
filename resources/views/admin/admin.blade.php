<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - La Cresta</title>
    <style>
        :root { --primary: #3b82f6; --danger: #ef4444; --success: #22c55e; --dark: #1e293b; --bg: #f4f4f9; }
        body { font-family: sans-serif; background-color: var(--bg); margin: 0; display: flex; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--dark); color: white; min-height: 100vh; position: fixed; padding: 20px; box-sizing: border-box; z-index: 100; }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 30px; border-bottom: 1px solid #334155; padding-bottom: 10px; color: #fb923c; }
        .nav-link { display: block; color: #cbd5e1; text-decoration: none; padding: 12px; border-radius: 8px; margin-bottom: 8px; cursor: pointer; transition: 0.2s; border: none; background: none; width: 100%; text-align: left; font-size: 1rem; }
        .nav-link:hover, .nav-link.active { background: #334155; color: white; }
        
        /* Main Content */
        .main-content { margin-left: 260px; flex: 1; padding: 30px; width: calc(100% - 260px); }
        .section { display: none; animation: fadeIn 0.3s ease; }
        .section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Alertas */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

        /* Stats & Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; border-bottom: 4px solid #e2e8f0; }
        .stat-card h3 { margin: 0; color: #666; font-size: 0.8rem; text-transform: uppercase; }
        .stat-card p { margin: 10px 0 0; font-size: 1.8rem; font-weight: bold; }
        .border-orange { border-color: #f97316; }

        /* Forms & Tables */
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .search-bar { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; font-size: 1rem; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f8f9fa; padding: 12px; border-bottom: 2px solid #eee; color: #475569; }
        td { padding: 12px; border-bottom: 1px solid #eee; }

        .btn { padding: 8px 15px; border-radius: 6px; border: none; font-weight: bold; cursor: pointer; font-size: 0.85rem; transition: 0.2s; }
        .btn-add { background: var(--primary); color: white; }
        .btn-edit { background: #e0f2fe; color: #0369a1; margin-right: 5px; }
        .btn-delete { background: #fee2e2; color: var(--danger); }
        .btn-logout { background: var(--danger); color: white; width: 100%; margin-top: 10px; }
        
        /* Admin Form Grid */
        .admin-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 20px; align-items: end; }
        .admin-form div { display: flex; flex-direction: column; gap: 5px; }
        .admin-form label { font-size: 0.8rem; font-weight: bold; color: #64748b; }
        .admin-form input, .admin-form select { padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem; }

        /* Edit Overlay Form */
        .edit-row { background: #f0f9ff !important; display: none; }
        .edit-row.active { display: table-row; }

        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; background: #e2e8f0; color: #475569; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>La Cresta Admin</h2>
    <button class="nav-link active" onclick="showSection('resum', this)">📊 Resum</button>
    <button class="nav-link" onclick="showSection('categories', this)">📂 Categories</button>
    <button class="nav-link" onclick="showSection('productes', this)">🍔 Productes</button>
    <button class="nav-link" onclick="showSection('treballadors', this)">👥 Treballadors</button>
    <button class="nav-link" onclick="showSection('comandes', this)">🧾 Historial</button>
    <hr style="border-color: #334155; margin: 20px 0;">
    <a href="{{ url('/') }}" class="nav-link">⬅ Anar al TPV</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-logout">TANCAR SESSIÓ</button>
    </form>
</div>

<div class="main-content">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <div id="resum" class="section active">
        <h1>Panell de Control</h1>
        <div class="stats-grid">
            <div class="stat-card border-orange"><h3>Total Avui</h3><p>{{ number_format($totalAvui, 2) }}€</p></div>
            <div class="stat-card"><h3>Comandes</h3><p>{{ $comandesComptador }}</p></div>
            <div class="stat-card"><h3>Millor Treballador</h3><p style="color: var(--primary)">{{ $millorWorker->name ?? 'Sense vendes' }}</p></div>
        </div>
    </div>

    <div id="categories" class="section">
        <h1>Gestió de Categories</h1>
        <div class="card">
            <h3>Afegir Nova Categoria</h3>
            <form action="{{ route('categories.store') }}" method="POST" class="admin-form" style="grid-template-columns: 1fr 120px;">
                @csrf
                <div>
                    <label>Nom de la categoria</label>
                    <input type="text" name="name" placeholder="Ex: Begudes, Hamburgueses..." required>
                </div>
                <button type="submit" class="btn btn-add">CREAR</button>
            </form>

            <table style="margin-top: 20px;">
                <thead><tr><th>Nom</th><th style="text-align: right;">Accions</th></tr></thead>
                <tbody>
                    @foreach($categories as $cat)
                    <tr>
                        <td><strong>{{ $cat->name }}</strong></td>
                        <td style="text-align: right;">
                            <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Si elimines la categoria, els productes es quedaran sense categoria. Continuar?')">
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

    <div id="productes" class="section">
        <h1>Gestió de Productes</h1>
        <div class="card">
            <h3>Afegir Nou Producte</h3>
            <form action="{{ route('products.store') }}" method="POST" class="admin-form">
                @csrf
                <div>
                    <label>Nom</label>
                    <input type="text" name="name" placeholder="Nom del producte" required>
                </div>
                <div>
                    <label>Preu (€)</label>
                    <input type="number" name="price" step="0.01" placeholder="8.50" required>
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
                <div>
                    <label>URL Imatge (Opcional)</label>
                    <input type="text" name="image" placeholder="https://...">
                </div>
                <button type="submit" class="btn btn-add">AFEGIR</button>
            </form>
        </div>

        <div class="card">
            <input type="text" id="productSearch" class="search-bar" placeholder="🔍 Cerca per nom de producte..." onkeyup="filterProducts()">
            
            <table id="productsTable">
                <thead>
                    <tr>
                        <th>Producte</th>
                        <th>Categoria</th>
                        <th>Preu</th>
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
                        <td>{{ number_format($product->price, 2) }}€</td>
                        <td style="text-align: right; display: flex; justify-content: flex-end; gap: 5px;">
                            <button class="btn btn-edit" onclick="toggleEdit('{{ $product->id }}')">Editar</button>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Eliminar?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-delete">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <tr id="edit-{{ $product->id }}" class="edit-row">
                        <td colspan="4">
                            <form action="{{ route('products.update', $product->id) }}" method="POST" style="display: flex; gap: 10px; padding: 10px;">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $product->name }}" required style="flex: 2;">
                                <input type="number" name="price" value="{{ $product->price }}" step="0.01" required style="width: 80px;">
                                <select name="category_id" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $product->categories->contains($cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-add">Desar</button>
                                <button type="button" class="btn btn-delete" onclick="toggleEdit('{{ $product->id }}')">X</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="treballadors" class="section">
        <h1>Gestió de Treballadors</h1>
        <div class="card">
            <h3>Afegir Nou Treballador</h3>
            <form action="{{ route('workers.store') }}" method="POST" class="admin-form" style="grid-template-columns: 1fr 1fr 120px;">
                @csrf
                <div>
                    <label>Nom complet</label>
                    <input type="text" name="name" placeholder="Nom complet" required>
                </div>
                <div>
                    <label>PIN (4 xifres)</label>
                    <input type="text" name="pin" placeholder="PIN opcional" pattern="\d{4}" title="4 números o buit">
                </div>
                <button type="submit" class="btn btn-add">AFEGIR</button>
            </form>

            <table>
                <thead><tr><th>Nom</th><th>PIN</th><th style="text-align: right;">Accions</th></tr></thead>
                <tbody>
                    @foreach($treballadors as $worker)
                    <tr>
                        <td><strong>{{ $worker->name }}</strong></td>
                       <td>
                            @if(!is_null($worker->pin) && $worker->pin !== '')
                                <small>✅ Actiu (<code>****</code>)</small>
                            @else
                                <small style="color: var(--danger)">❌ No en té</small>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <form action="{{ route('workers.destroy', $worker->id) }}" method="POST" onsubmit="return confirm('Eliminar?')">
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

    <div id="comandes" class="section">
        <h1>Historial de Vendes</h1>
        <div class="card">
            <table>
                <thead><tr><th>Hora</th><th>Treballador</th><th>Total</th><th style="text-align: right;">Accions</th></tr></thead>
                <tbody>
                    @forelse($darreresVendes as $venda)
                    <tr>
                        <td>{{ $venda->created_at->format('H:i') }}h</td>
                        <td>{{ $venda->worker->name ?? 'Sistema' }}</td>
                        <td><strong>{{ number_format($venda->total_price, 2) }}€</strong></td>
                        <td style="text-align: right;">
                            <form action="{{ url('/admin/orders/'.$venda->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-delete">ANUL·LAR</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align: center;">Cap venda avui.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function showSection(id, element) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        element.classList.add('active');
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

    // Afegeix això dins del teu script actual
window.addEventListener('DOMContentLoaded', (event) => {
    // Mirem si la URL té una àncora (ex: #productes)
    const hash = window.location.hash;
    
    if (hash) {
        // Netegem el '#' del nom per quedar-nos amb 'productes'
        const sectionId = hash.substring(1); 
        
        // Busquem el botó que té el onclick amb aquest ID
        const targetButton = document.querySelector(`button[onclick*="${sectionId}"]`);
        
        if (targetButton) {
            // Cridem a la teva funció showSection pasant-li l'ID i el botó
            showSection(sectionId, targetButton);
        }
    }
});
</script>

</body>
</html>