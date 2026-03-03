<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Admin - La Cresta</title>
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
        
        /* Sidebar Estil La Cresta */
        .sidebar { 
            width: 280px; 
            background: var(--sidebar-bg); 
            min-height: 100vh; 
            position: fixed; 
            padding: 25px 20px; 
            box-sizing: border-box; 
            z-index: 100; 
            border-right: 1px solid #eee;
            display: flex;
            flex-direction: column;
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

        .nav-link:hover { background: #fafafa; color: #000; }
        .nav-link.active { 
            background: rgba(255, 237, 5, 0.15); 
            color: #000; 
            font-weight: 700;
        }
        
        /* Main Content */
        .main-content { margin-left: 280px; flex: 1; padding: 40px; width: calc(100% - 280px); }
        h1 { font-weight: 900; text-transform: uppercase; letter-spacing: -0.5px; margin-bottom: 30px; }
        
        .section { display: none; animation: fadeIn 0.3s ease; }
        .section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Alertas */
        .alert { padding: 15px; margin-bottom: 25px; border-radius: 12px; font-weight: bold; border-left: 5px solid; }
        .alert-success { background: #dcfce7; color: #166534; border-color: #22c55e; }

        /* Stats & Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { 
            background: white; padding: 25px; border-radius: 20px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.02); text-align: left; 
            border: 1px solid #eee; transition: 0.3s;
        }
        .stat-card h3 { margin: 0; color: #999; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card p { margin: 10px 0 0; font-size: 2rem; font-weight: 900; color: #333; }
        .border-orange { border-top: 4px solid var(--primary); }

        /* Forms & Tables */
        .card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 30px; border: 1px solid #eee; }
        .search-bar { width: 100%; padding: 15px; border: 1px solid #eee; border-radius: 12px; margin-bottom: 25px; font-size: 1rem; background: #fafafa; }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th { text-align: left; padding: 15px; color: #aaa; text-transform: uppercase; font-size: 0.75rem; }
        td { padding: 15px; background: white; border-top: 1px solid #f8f8f8; border-bottom: 1px solid #f8f8f8; }
        td:first-child { border-left: 1px solid #f8f8f8; border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        td:last-child { border-right: 1px solid #f8f8f8; border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

        .btn { padding: 10px 20px; border-radius: 10px; border: none; font-weight: 800; cursor: pointer; font-size: 0.8rem; transition: 0.2s; text-transform: uppercase; }
        .btn-add { background: var(--primary); color: #000; }
        .btn-edit { background: #f0f0f0; color: #666; margin-right: 5px; }
        .btn-delete { background: #fff0f0; color: var(--danger); }
        .btn-logout { background: #fdf2f2; color: #e04a4a; border: 1px solid #fee2e2; width: 100%; margin-top: 20px; }
        
        .admin-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; align-items: end; }
        .admin-form label { font-size: 0.75rem; font-weight: 800; color: #999; text-transform: uppercase; margin-bottom: 5px; display: block; }
        .admin-form input, .admin-form select { padding: 12px; border: 1px solid #eee; border-radius: 10px; font-size: 0.95rem; background: #fafafa; font-family: inherit; }

        .edit-row { background: #fffbeb !important; display: none; }
        .edit-row.active { display: table-row; }
        .badge { padding: 6px 12px; border-radius: 8px; font-size: 0.7rem; background: #f0f0f0; color: #666; font-weight: 700; text-transform: uppercase; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/logo-la-cresta-sense-fons.png') }}" alt="Logo La Cresta">
        <div></div>
    </div>
    
    <button class="nav-link active" onclick="showSection('resum', this)">📊 Resum</button>
    <button class="nav-link" onclick="showSection('categories', this)">📂 Categories</button>
    <button class="nav-link" onclick="showSection('productes', this)">🍔 Productes</button>
    <button class="nav-link" onclick="showSection('treballadors', this)">👥 Treballadors</button>
    <button class="nav-link" onclick="showSection('comandes', this)">🧾 Historial</button>
    
    
    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
    
    <a href="{{ url('/') }}" class="nav-link" style="color: var(--primary-dark)">⬅ Anar al TPV</a>
    
    <form method="POST" action="{{ route('logout') }}" style="margin-top: auto;">
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
            <div class="stat-card"><h3>Millor Treballador</h3><p style="color: var(--success)">{{ $millorWorker->name ?? 'Sense vendes' }}</p></div>
        </div>
    </div>

    <div id="categories" class="section">
        <h1>Gestió de Categories</h1>
        <div class="card">
            <label style="font-weight: 900; margin-bottom: 10px; display: block;">AFEGIR CATEGORIA</label>
            <form action="{{ route('categories.store') }}" method="POST" class="admin-form" style="grid-template-columns: 1fr 120px;">
                @csrf
                <div>
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
                            <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Eliminar?')">
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
                        <td><strong style="color: var(--success)">{{ number_format($product->price, 2) }}€</strong></td>
                        <td style="text-align: right;">
                            <button class="btn btn-edit" onclick="toggleEdit('{{ $product->id }}')">Editar</button>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Eliminar?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-delete">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <tr id="edit-{{ $product->id }}" class="edit-row">
                        <td colspan="4">
                            <form action="{{ route('products.update', $product->id) }}" method="POST" style="display: flex; gap: 10px; padding: 10px;">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $product->name }}" required style="flex: 2; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                                <input type="number" name="price" value="{{ $product->price }}" step="0.01" required style="width: 80px; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                                <select name="category_id" required style="padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $product->categories->contains($cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-add" style="padding: 5px 15px;">OK</button>
                                <button type="button" class="btn btn-delete" onclick="toggleEdit('{{ $product->id }}')" style="padding: 5px 15px;">X</button>
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
            <label style="font-weight: 900; margin-bottom: 10px; display: block;">NOU TREBALLADOR</label>
            <form action="{{ route('workers.store') }}" method="POST" class="admin-form" style="grid-template-columns: 1fr 1fr 120px;">
                @csrf
                <div>
                    <label>Nom complet</label>
                    <input type="text" name="name" required>
                </div>
                <div>
                    <label>PIN (4 xifres)</label>
                    <input type="text" name="pin" pattern="\d{4}">
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
                                <span class="badge" style="background:#e6fffa; color:#2c7a7b;">✅ ACTIU</span>
                            @else
                                <span class="badge" style="background:#fff5f5; color:#c53030;">❌ NO PIN</span>
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
                        <td><strong>{{ $venda->worker->name ?? 'Sistema' }}</strong></td>
                        <td><strong style="color:var(--success)">{{ number_format($venda->total_price, 2) }}€</strong></td>
                        <td style="text-align: right;">
                            <form action="{{ url('/admin/orders/'.$venda->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-delete">ANUL·LAR</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align: center; padding: 20px;">Cap venda avui.</td></tr>
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
        window.location.hash = id;
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

    window.addEventListener('DOMContentLoaded', (event) => {
        const hash = window.location.hash.substring(1); 
        if (hash) {
            const targetButton = document.querySelector(`button[onclick*="${hash}"]`);
            if (targetButton) showSection(hash, targetButton);
        }
    });
</script>

</body>
</html>