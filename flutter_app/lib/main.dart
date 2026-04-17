import 'package:flutter/material.dart';

import 'core/network/api_client.dart';
import 'core/theme/tpv_theme.dart';
import 'features/auth/data/auth_service.dart';
import 'features/tpv/presentation/tpv_page_modern.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({
    super.key,
    this.enableSessionRestore = true,
  });

  final bool enableSessionRestore;

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Quickserve Flutter',
      theme: TpvTheme.build(),
      home: AuthGate(enableSessionRestore: enableSessionRestore),
    );
  }
}

class AuthGate extends StatefulWidget {
  const AuthGate({
    super.key,
    this.enableSessionRestore = true,
  });

  final bool enableSessionRestore;

  @override
  State<AuthGate> createState() => _AuthGateState();
}

class _AuthGateState extends State<AuthGate> {
  final AuthService _authService = AuthService(ApiClient());
  Map<String, dynamic>? _loggedUser;
  bool _checkingSession = true;

  @override
  void initState() {
    super.initState();
    if (widget.enableSessionRestore) {
      _restoreSession();
    } else {
      _checkingSession = false;
    }
  }

  Future<void> _restoreSession() async {
    try {
      final Map<String, dynamic> user = await _authService.me();
      if (!mounted) return;
      setState(() {
        _loggedUser = user;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loggedUser = null;
      });
    } finally {
      if (mounted) {
        setState(() {
          _checkingSession = false;
        });
      }
    }
  }

  Future<void> _onLoginSuccess(Map<String, dynamic> user) async {
    setState(() {
      _loggedUser = user;
    });
  }

  Future<void> _logout() async {
    await _authService.logout();
    if (!mounted) return;
    setState(() {
      _loggedUser = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_checkingSession) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (_loggedUser == null) {
      return LoginPage(authService: _authService, onLoggedIn: _onLoginSuccess);
    }

    return TpvPage(
      authService: _authService,
      userName: (_loggedUser!['name'] ?? 'Usuari').toString(),
      onLogout: _logout,
    );
  }
}

class LoginPage extends StatefulWidget {
  const LoginPage({
    super.key,
    required this.authService,
    required this.onLoggedIn,
  });

  final AuthService authService;
  final Future<void> Function(Map<String, dynamic> user) onLoggedIn;

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  String _status = 'Introduce credenciales para iniciar sesion';
  bool _busy = false;

  Future<void> _login() async {
    setState(() {
      _busy = true;
      _status = 'Iniciando sesion...';
    });

    try {
      await widget.authService.login(
        email: _emailController.text.trim(),
        password: _passwordController.text,
      );
      final Map<String, dynamic> user = await widget.authService.me();
      await widget.onLoggedIn(user);
    } catch (error) {
      if (!mounted) return;
      setState(() {
        _status = 'Error de login: $error';
      });
    } finally {
      if (mounted) {
        setState(() {
          _busy = false;
        });
      }
    }
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Quickserve Login')),
      body: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 420),
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: <Widget>[
                TextField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  decoration: const InputDecoration(labelText: 'Email'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _passwordController,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'Password'),
                ),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: _busy ? null : _login,
                  child: const Text('Entrar'),
                ),
                const SizedBox(height: 12),
                Text(_status, textAlign: TextAlign.center),
                if (_busy) ...<Widget>[
                  const SizedBox(height: 12),
                  const Center(child: CircularProgressIndicator()),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
