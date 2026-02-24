<x-guest-layout>
    <style>
        /* Contenidor del logo */
        .logo-container {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
        }
        .logo-container img {
            max-width: 200px; /* Ajusta la mida segons vulguis */
            height: auto;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.5)); /* Li dóna profunditat al logo */
        }

        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; color: #aaa; font-size: 0.65rem; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; margin-left: 5px; }
        .form-group input { 
            width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); 
            border-radius: 12px; padding: 12px 15px; color: #fff; font-size: 0.9rem; box-sizing: border-box; outline: none; transition: 0.3s;
        }
        .form-group input:focus { border-color: #ffed05; background: rgba(255,102,0,0.05); }
        .btn-login { 
            width: 100%; background: linear-gradient(to right, #b6aa02, #ffed05); border: none; 
            border-radius: 12px; padding: 15px; color: #000; font-weight: 900; cursor: pointer; 
            text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; transition: 0.3s;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,237,5,0.4); }
        .extra-links { display: flex; justify-content: space-between; font-size: 0.7rem; color: #666; margin-top: 15px; }
        .extra-links a { color: #ffed05; text-decoration: none; }
    </style>

    <div class="logo-container">
        <img src="{{ asset('images/logo-la-cresta-sense-fons.png') }}" alt="Logo La Cresta">
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label>Usuari Administrador</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="correu@lacresta.com">
        </div>

        <div class="form-group">
            <label>Contrasenya</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>

        <div class="extra-links">
            <label style="cursor: pointer; display: flex; align-items: center;">
                <input type="checkbox" name="remember" style="margin-right: 5px;"> Recorda'm
            </label>
            <a href="{{ route('password.request') }}">¿Has oblidat la clau?</a>
        </div>

        <button type="submit" class="btn-login">Entrar al Sistema</button>
    </form>
</x-guest-layout>