import 'package:dio/dio.dart';

import '../../../core/network/api_client.dart';

class HealthService {
  HealthService(this._apiClient);

  final ApiClient _apiClient;

  Future<String> ping() async {
    final Response<dynamic> response = await _apiClient.dio.get('/ping');
    final dynamic data = response.data;

    if (data is Map<String, dynamic> && data['ok'] == true) {
      return 'API OK (${data['service'] ?? 'service'})';
    }

    throw Exception('Unexpected response from API');
  }
}
