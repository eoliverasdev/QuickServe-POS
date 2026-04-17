import 'package:dio/dio.dart';

import '../../../core/network/api_client.dart';
import '../../../core/network/api_endpoints.dart';
import '../../auth/data/auth_service.dart';
import '../domain/admin_models.dart';

class AdminService {
  AdminService(this._apiClient, this._authService);

  final ApiClient _apiClient;
  final AuthService _authService;

  Future<String> _requireToken() async {
    final String? token = await _authService.getStoredToken();
    if (token == null || token.isEmpty) {
      throw Exception('Token no disponible');
    }
    return token;
  }

  Options _auth(String token) {
    return Options(headers: <String, dynamic>{'Authorization': 'Bearer $token'});
  }

  /// Verifies the PIN against a worker. Returns worker name on success,
  /// throws with a readable message otherwise.
  Future<String> verifyPin(String pin) async {
    final String token = await _requireToken();
    try {
      final Response<dynamic> response = await _apiClient.dio.post(
        ApiEndpoints.adminVerifyPin,
        data: <String, dynamic>{'pin': pin},
        options: _auth(token),
      );
      final dynamic data = response.data;
      if (data is Map<String, dynamic> && data['ok'] == true) {
        final Map<String, dynamic> worker = (data['worker'] as Map<String, dynamic>? ?? <String, dynamic>{});
        return worker['name']?.toString() ?? 'Admin';
      }
      throw Exception('PIN incorrecte');
    } on DioException catch (err) {
      final dynamic data = err.response?.data;
      if (data is Map<String, dynamic> && data['error'] is String) {
        throw Exception(data['error']);
      }
      throw Exception('PIN incorrecte');
    }
  }

  Future<AdminDashboardData> fetchDashboard() async {
    final String token = await _requireToken();
    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.adminDashboard,
      options: _auth(token),
    );
    final dynamic data = response.data;
    if (data is! Map<String, dynamic>) {
      throw Exception('Resposta de dashboard invàlida');
    }
    return AdminDashboardData.fromJson(data);
  }
}
