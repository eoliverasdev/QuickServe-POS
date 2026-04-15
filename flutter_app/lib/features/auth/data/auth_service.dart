import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../../core/network/api_client.dart';
import '../../../core/network/api_endpoints.dart';

class AuthService {
  AuthService(this._apiClient);

  static const String tokenStorageKey = 'auth_token';
  final ApiClient _apiClient;

  Future<String?> getStoredToken() async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    return prefs.getString(tokenStorageKey);
  }

  Future<void> saveToken(String token) async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.setString(tokenStorageKey, token);
  }

  Future<void> clearToken() async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.remove(tokenStorageKey);
  }

  Future<void> login({
    required String email,
    required String password,
  }) async {
    final Response<dynamic> response = await _apiClient.dio.post(
      ApiEndpoints.authLogin,
      data: <String, dynamic>{
        'email': email,
        'password': password,
        'device_name': 'flutter-web',
      },
    );

    final dynamic data = response.data;
    if (data is! Map<String, dynamic> || data['token'] is! String) {
      throw Exception('Respuesta de login invalida');
    }

    await saveToken(data['token'] as String);
  }

  Future<Map<String, dynamic>> me() async {
    final String? token = await getStoredToken();
    if (token == null || token.isEmpty) {
      throw Exception('No hay token guardado');
    }

    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.authMe,
      options: Options(
        headers: <String, dynamic>{
          'Authorization': 'Bearer $token',
        },
      ),
    );

    final dynamic data = response.data;
    if (data is! Map<String, dynamic> || data['user'] is! Map<String, dynamic>) {
      throw Exception('Respuesta de perfil invalida');
    }

    return data['user'] as Map<String, dynamic>;
  }

  Future<void> logout() async {
    final String? token = await getStoredToken();
    if (token != null && token.isNotEmpty) {
      try {
        await _apiClient.dio.post(
          ApiEndpoints.authLogout,
          options: Options(
            headers: <String, dynamic>{
              'Authorization': 'Bearer $token',
            },
          ),
        );
      } catch (_) {
        // Ignore server errors and clear local token anyway.
      }
    }

    await clearToken();
  }
}
