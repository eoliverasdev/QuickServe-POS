import 'package:flutter/material.dart';

import 'core/config/app_config.dart';
import 'core/network/api_client.dart';
import 'features/health/data/health_service.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({
    super.key,
    this.enableHealthCheck = true,
  });

  final bool enableHealthCheck;

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Quickserve Flutter',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.indigo),
      ),
      home: HealthCheckPage(autoCheck: enableHealthCheck),
    );
  }
}

class HealthCheckPage extends StatefulWidget {
  const HealthCheckPage({
    super.key,
    this.autoCheck = true,
  });

  final bool autoCheck;

  @override
  State<HealthCheckPage> createState() => _HealthCheckPageState();
}

class _HealthCheckPageState extends State<HealthCheckPage> {
  late final HealthService _healthService;
  String _status = 'Comprobando conexion...';
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _healthService = HealthService(ApiClient());
    if (widget.autoCheck) {
      _checkApi();
    } else {
      _loading = false;
      _status = 'Comprobacion manual lista';
    }
  }

  Future<void> _checkApi() async {
    setState(() {
      _loading = true;
      _status = 'Comprobando conexion...';
    });

    try {
      final String result = await _healthService.ping();
      setState(() {
        _status = '$result\n${AppConfig.apiBaseUrl}/ping';
      });
    } catch (error) {
      setState(() {
        _status = 'No se pudo conectar con Laravel API.\n'
            'URL actual: ${AppConfig.apiBaseUrl}\n'
            'Error: $error';
      });
    } finally {
      setState(() {
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final bool isOk = _status.startsWith('API OK');

    return Scaffold(
      appBar: AppBar(title: const Text('Quickserve API check')),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: <Widget>[
              if (_loading) const CircularProgressIndicator(),
              if (!_loading)
                Icon(
                  isOk ? Icons.check_circle : Icons.error,
                  size: 44,
                  color: isOk ? Colors.green : Colors.red,
                ),
              const SizedBox(height: 16),
              Text(
                _status,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              OutlinedButton(
                onPressed: _checkApi,
                child: const Text('Reintentar'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
