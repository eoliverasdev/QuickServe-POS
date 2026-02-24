<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>La Cresta TPV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body, html { margin: 0; padding: 0; height: 100%; font-family: 'Segoe UI', sans-serif; background: #000; }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Hem canviat la URL d'Unsplash per la teva imatge local */
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.8)), 
                        url("{{ asset('images/lacrestatpv.jpg') }}");
            background-size: cover;
            background-position: center;
            padding: 20px;
        }
        .glass-card {
            background: rgba(15, 15, 15, 0.75);
            border: 1px solid rgba(255, 237, 5, 0.2); /* Un toc groc subtil a la vora */
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(12px);
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="glass-card">
            {{ $slot }}
        </div>
    </div>
</body>
</html>