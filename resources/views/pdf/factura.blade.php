<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 5px; border-bottom: 1px solid #ddd; }
        .total { text-align: right; font-size: 16px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LA CRESTA</h1>
        <p>Rostisseria i Menjars Preparats</p>
        <p>Data: {{ $date }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Producte</th>
                <th style="text-align: right;">Preu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td style="text-align: right;">{{ number_format($item['price'], 2) }} €</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        TOTAL: {{ number_format($total, 2) }} €
    </div>
</body>
</html>