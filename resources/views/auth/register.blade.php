<x-guest-layout>
    <style>
        .logo-container {
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
        }
        .logo-container img {
            max-width: 160px; /* Una mica més petit per deixar espai al formulari */
            height: auto;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.5));
        }

        .form-group { text-align: left; margin-bottom: 15px; }
        .form-group label { display: block; color: #aaa; font-size: 0.65rem; font-weight: bold; text-transform: uppercase; margin-bottom: 6px; margin-left: 5px; }
        .form-group input { 
            width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); 
            border-radius: 12px; padding: 10px 15px; color: #fff; font-size: 0.9rem; box-sizing: border-box; outline: none; transition: 0.3s;
        }
        .form-group input:focus { border-color: #ffed05; background: rgba(255,102,0,0.05); }
        
        .btn-register-submit { 
            width: 100%; background: linear-gradient(to right, #b6aa02, #ffed05); border: none; 
            border-radius: 12px; padding: 14px; color: #000; font-weight: 900; cursor: pointer; 
            text-transform: uppercase; letter-spacing: 1px; margin-top: 15px; transition: 0.3s;
        }
        .btn-register-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,237,5,0.4); }

        .login-link {
            display: block; text-align: center; margin-top: 20px; font-size: 0.75rem; color: #666; text-decoration: none; transition: 0.3s;
        }
        .login-link span { color: #ffed05; font-weight: bold; }
        .login-link:hover { color: #888; }

        /* Ajust per errors de validació */
        .error-msg { color: #ff4444; font-size: 0.7rem; margin-top: 5px; margin-left: 5px; }
    </style>

    <div class="logo-container">
        <img src="{{ asset('images/logo-la-cresta-sense-fons.png') }}" alt="Logo La Cresta">
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label>Nom Complet</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Ex: Administrador La Cresta">
            <x-input-error :messages="$errors->get('name')" class="error-msg" />
        </div>

        <div class="form-group">
            <label>Correu Electrònic</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="correu@lacresta.com">
            <x-input-error :messages="$errors->get('email')" class="error-msg" />
        </div>

        <div class="form-group">
            <label>Contrasenya</label>
            <input id="password" type="password" name="password" required placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="error-msg" />
        </div>

        <div class="form-group">
            <label>Confirmar Contrasenya</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••">
            <x-input-error :messages="$errors->get('password_confirmation')" class="error-msg" />
        </div>

        <button type="submit" class="btn-register-submit">Crear compte</button>

        <a class="login-link" href="{{ route('login') }}">
            ¿Ja tens un compte? <span>Inicia sessió</span>
        </a>
    </form>
</x-guest-layout>