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
        .sidebar { width: 260px; background: var(--dark); color: white; min-height: 100vh; position: fixed; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 30px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .nav-link { display: block; color: #cbd5e1; text-decoration: none; padding: 12px; border-radius: 8px; margin-bottom: 8px; cursor: pointer; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: #334155; color: white; }
        .sidebar form { margin-top: 20px; }

        /* Main Content */
        .main-content { margin-left: 260px; flex: 1; padding: 30px; width: calc(100% - 260px); }
        .section { display: none; }
        .section.active { display: block; }

        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Stats & Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; }
        .stat-card h3 { margin: 0; color: #666; font-size: 0.8rem; text-transform: uppercase; }
        .stat-card p { margin: 10px 0 0; font-size: 1.8rem; font-weight: bold; }
        .price { color: var(--success); }

        /* Tables & Forms */
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f8f9fa; padding: 12px; border-bottom: 2px solid #eee; }
        td { padding: 12px; border-bottom: 1px solid #eee; }

        .btn { padding: 8px 15px; border-radius: 6px; border: none; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 0.9rem; }
        .btn-add { background: var(--primary); color: white; }
        .btn-delete { background: #fee2e2; color: var(--danger); }
        .btn-logout { background: var(--danger); color: white; width: 100%; }
        
        /* Form inline */
        .admin-form { display: grid; grid-template-columns: 1fr 1fr 100px; gap: 10px; margin-bottom: 20px; }
        .admin-form input, .admin-form select { padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>La Cresta Admin</h2>
    <div class="nav-link active" onclick="showSection('resum')">📊 Resum</div>
    <div class="nav-link" onclick="showSection('productes')">🍔 Productes</div>
    <div class="nav-link" onclick="showSection('treballadors')">👥 Treballadors</div>
    <div class="nav-link" onclick="showSection('comandes')">🧾 Historial Vendes</div>
    <hr style="border-color: #334155;">
    <a href="{{ url('/') }}" class="nav-link">⬅ Anar al TPV</a>
    
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-logout">TANCAR SESSIÓ</button>
    </form>
</div>

<div class="main-content">
    
    <div id="resum" class="section active">
        <header>
            <h1>Panell de Control</h1>
            <div class="date">{{ now()->format('d/m/Y') }}</div>
        </header>
        <div class="stats-grid">
            <div class="stat-card"><h3>Total Avui</h3><p class="price">{{ number_format($totalAvui, 2) }}€</p></div>
            <div class="stat-card"><h3>Comandes</h3><p>{{ $comandesComptador }}</p></div>
            <div class="stat-card"><h3>Millor Treballador</h3><p>{{ $millorWorker->name ?? '---' }}</p></div>
        </div>
    </div>

    <div id="productes" class="section">
        <h1>Gestió de Productes</h1>
        <div class="card">
            <h3>Editar / Nou Producte</h3>
            <p><small>Pots modificar preus o noms directament a la llista (requereix programar el controlador).</small></p>
            <table>
                <thead>
                    <tr>
                        <th>Producte</th>
                        <th>Preu</th>
                        <th>Accions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productes as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ number_format($product->price, 2) }}€</td>
                        <td>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Segur?')">
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

    <div id="treballadors" class="section">
        <h1>Gestió de Treballadors</h1>
        <div class="card">
            <h3>Afegir Nou Treballador</h3>
            <form action="{{ route('workers.store') }}" method="POST" class="admin-form">
                @csrf
                <input type="text" name="name" placeholder="Nom" required>
                <input type="text" name="pin" placeholder="PIN (4 dígits)" maxlength="4" required>
                <button type="submit" class="btn btn-add">AFEGIR</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>PIN</th>
                        <th>Accions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($treballadors as $worker)
                    <tr>
                        <td>{{ $worker->name }}</td>
                        <td><code>****</code></td>
                        <td>
                            <form action="{{ route('workers.destroy', $worker->id) }}" method="POST" onsubmit="return confirm('Segur?')">
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
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Treballador</th>
                        <th>Productes</th>
                        <th>Total</th>
                        <th>Accions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($darreresVendes as $venda)
                    <tr>
                        <td>{{ $venda->created_at->format('H:i') }}</td>
                        <td>{{ $venda->worker->name ?? 'Desconegut' }}</td>
                        <td>
                            @foreach($venda->items as $item)
                                <small><strong>{{ $item->quantity }}x</strong> {{ $item->product->name ?? '---' }}</small><br>
                            @endforeach
                        </td>
                        <td><strong>{{ number_format($venda->total_price, 2) }}€</strong></td>
                        <td><button class="btn btn-delete" style="font-size: 0.7rem;" onclick="confirmVoid('{{ $venda->id }}')">ANUL·LAR</button></td>
                    </tr>
                    @empty
                    <tr><td colspan="5">No hi ha vendes avui.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function showSection(id) {
        // Amagar totes
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        
        // Mostrar la triada
        document.getElementById(id).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    function confirmVoid(orderId) {
        if (confirm('Vols anul·lar la venda #' + orderId + '?')) {
            // Aquí enviaries la petició per esborrar la comanda
            console.log('Anul·lant...', orderId);
        }
    }
</script>

</body>
</html>