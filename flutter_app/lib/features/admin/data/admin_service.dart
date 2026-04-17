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

  // ── Categories ─────────────────────────────────────────────────────────

  Future<List<AdminCategory>> fetchCategories() async {
    final String token = await _requireToken();
    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.adminCategories,
      options: _auth(token),
    );
    final dynamic data = response.data;
    if (data is! Map<String, dynamic> || data['categories'] is! List) {
      throw Exception('Resposta de categories invàlida');
    }
    return (data['categories'] as List<dynamic>)
        .whereType<Map<String, dynamic>>()
        .map(AdminCategory.fromJson)
        .toList();
  }

  Future<AdminCategory> createCategory({required String name, String? color}) async {
    final String token = await _requireToken();
    try {
      final Response<dynamic> response = await _apiClient.dio.post(
        ApiEndpoints.adminCategories,
        data: <String, dynamic>{
          'name': name,
          if (color != null && color.isNotEmpty) 'color': color,
        },
        options: _auth(token),
      );
      final dynamic data = response.data;
      if (data is Map<String, dynamic> && data['category'] is Map<String, dynamic>) {
        return AdminCategory.fromJson(data['category'] as Map<String, dynamic>);
      }
      throw Exception('Resposta invàlida');
    } on DioException catch (err) {
      throw Exception(_parseValidationError(err, 'No s\'ha pogut crear la categoria'));
    }
  }

  Future<AdminCategory> updateCategory({
    required int id,
    required String name,
    String? color,
  }) async {
    final String token = await _requireToken();
    try {
      final Response<dynamic> response = await _apiClient.dio.put(
        ApiEndpoints.adminCategory(id),
        data: <String, dynamic>{
          'name': name,
          if (color != null && color.isNotEmpty) 'color': color,
        },
        options: _auth(token),
      );
      final dynamic data = response.data;
      if (data is Map<String, dynamic> && data['category'] is Map<String, dynamic>) {
        return AdminCategory.fromJson(data['category'] as Map<String, dynamic>);
      }
      throw Exception('Resposta invàlida');
    } on DioException catch (err) {
      throw Exception(_parseValidationError(err, 'No s\'ha pogut actualitzar la categoria'));
    }
  }

  Future<void> deleteCategory(int id) async {
    final String token = await _requireToken();
    try {
      await _apiClient.dio.delete(
        ApiEndpoints.adminCategory(id),
        options: _auth(token),
      );
    } on DioException catch (err) {
      throw Exception(_parseValidationError(err, 'No s\'ha pogut eliminar la categoria'));
    }
  }

  // ── Products ───────────────────────────────────────────────────────────

  Future<List<AdminProduct>> fetchProducts() async {
    final String token = await _requireToken();
    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.adminProducts,
      options: _auth(token),
    );
    final dynamic data = response.data;
    if (data is! Map<String, dynamic> || data['products'] is! List) {
      throw Exception('Resposta de productes invàlida');
    }
    return (data['products'] as List<dynamic>)
        .whereType<Map<String, dynamic>>()
        .map(AdminProduct.fromJson)
        .toList();
  }

  Future<AdminProduct> saveProduct({
    int? id,
    required String name,
    required double price,
    required int stock,
    required int categoryId,
    bool isGlutenFree = false,
    bool active = true,
    String? description,
    String? imagePath,
  }) async {
    final String token = await _requireToken();
    final Map<String, dynamic> payload = <String, dynamic>{
      'name': name,
      'price': price,
      'stock': stock,
      'category_id': categoryId,
      'is_gluten_free': isGlutenFree,
      'active': active,
      if (description != null && description.isNotEmpty) 'description': description,
      if (imagePath != null && imagePath.isNotEmpty) 'image_path': imagePath,
    };
    try {
      final Response<dynamic> response = id == null
          ? await _apiClient.dio.post(ApiEndpoints.adminProducts, data: payload, options: _auth(token))
          : await _apiClient.dio.put(ApiEndpoints.adminProduct(id), data: payload, options: _auth(token));
      final dynamic data = response.data;
      if (data is Map<String, dynamic> && data['product'] is Map<String, dynamic>) {
        return AdminProduct.fromJson(data['product'] as Map<String, dynamic>);
      }
      throw Exception('Resposta invàlida');
    } on DioException catch (err) {
      throw Exception(_parseValidationError(err, 'No s\'ha pogut guardar el producte'));
    }
  }

  /// Uploads an image (from bytes) to the backend and returns the stored
  /// relative path (e.g. "images/productes/foo.jpg").
  Future<String> uploadProductImage({
    required List<int> bytes,
    required String filename,
  }) async {
    final String token = await _requireToken();
    final FormData form = FormData.fromMap(<String, dynamic>{
      'file': MultipartFile.fromBytes(bytes, filename: filename),
    });
    try {
      final Response<dynamic> response = await _apiClient.dio.post(
        ApiEndpoints.adminProductsUpload,
        data: form,
        options: Options(
          headers: <String, dynamic>{
            'Authorization': 'Bearer $token',
          },
          contentType: 'multipart/form-data',
        ),
      );
      final dynamic data = response.data;
      if (data is Map<String, dynamic> && data['path'] is String) {
        return data['path'] as String;
      }
      throw Exception('Resposta invàlida');
    } on DioException catch (err) {
      throw Exception(_parseValidationError(err, 'No s\'ha pogut pujar la imatge'));
    }
  }

  Future<void> deleteProduct(int id) async {
    final String token = await _requireToken();
    try {
      await _apiClient.dio.delete(
        ApiEndpoints.adminProduct(id),
        options: _auth(token),
      );
    } on DioException catch (err) {
      throw Exception(_parseValidationError(err, 'No s\'ha pogut eliminar el producte'));
    }
  }

  // ── Workers ────────────────────────────────────────────────────────────

  Future<List<AdminWorker>> fetchWorkers() async {
    final String token = await _requireToken();
    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.adminWorkers,
      options: _auth(token),
    );
    final dynamic data = response.data;
    if (data is! Map<String, dynamic> || data['workers'] is! List) {
      throw Exception('Resposta de treballadors invàlida');
    }
    return (data['workers'] as List<dynamic>)
        .whereType<Map<String, dynamic>>()
        .map(AdminWorker.fromJson)
        .toList();
  }

  Future<AdminWorker> saveWorker({
    int? id,
    required String name,
    String? pin,
    bool active = true,
  }) async {
    final String token = await _requireToken();
    final Map<String, dynamic> payload = <String, dynamic>{
      'name': name,
      'active': active,
      if (pin != null && pin.isNotEmpty) 'pin': pin,
    };
    try {
      final Response<dynamic> response = id == null
          ? await _apiClient.dio.post(ApiEndpoints.adminWorkers, data: payload, options: _auth(token))
          : await _apiClient.dio.put(ApiEndpoints.adminWorker(id), data: payload, options: _auth(token));
      final dynamic data = response.data;
      if (data is Map<String, dynamic> && data['worker'] is Map<String, dynamic>) {
        return AdminWorker.fromJson(data['worker'] as Map<String, dynamic>);
      }
      throw Exception('Resposta invàlida');
    } on DioException catch (err) {
      throw Exception(_parseValidationError(err, 'No s\'ha pogut guardar el treballador'));
    }
  }

  Future<void> deleteWorker(int id) async {
    final String token = await _requireToken();
    try {
      await _apiClient.dio.delete(
        ApiEndpoints.adminWorker(id),
        options: _auth(token),
      );
    } on DioException catch (err) {
      throw Exception(_parseValidationError(err, 'No s\'ha pogut eliminar el treballador'));
    }
  }

  // ── Orders history ─────────────────────────────────────────────────────

  Future<AdminOrdersPage> fetchOrders({
    int page = 1,
    int perPage = 20,
    String? status,
    String? paymentMethod,
    DateTime? from,
    DateTime? to,
    int? workerId,
    String? search,
  }) async {
    final String token = await _requireToken();
    final Map<String, dynamic> query = <String, dynamic>{
      'page': page,
      'per_page': perPage,
      if (status != null && status.isNotEmpty) 'status': status,
      if (paymentMethod != null && paymentMethod.isNotEmpty) 'payment_method': paymentMethod,
      if (from != null) 'from': _formatDate(from),
      if (to != null) 'to': _formatDate(to),
      if (workerId != null) 'worker_id': workerId,
      if (search != null && search.isNotEmpty) 'search': search,
    };
    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.adminOrders,
      queryParameters: query,
      options: _auth(token),
    );
    final dynamic data = response.data;
    if (data is! Map<String, dynamic>) {
      throw Exception('Resposta d\'històric invàlida');
    }
    return AdminOrdersPage.fromJson(data);
  }

  Future<AdminOrderDetail> fetchOrderDetail(int id) async {
    final String token = await _requireToken();
    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.adminOrder(id),
      options: _auth(token),
    );
    final dynamic data = response.data;
    if (data is! Map<String, dynamic> || data['order'] is! Map<String, dynamic>) {
      throw Exception('Resposta invàlida');
    }
    return AdminOrderDetail.fromJson(data['order'] as Map<String, dynamic>);
  }

  Future<void> deleteOrder(int id) async {
    final String token = await _requireToken();
    try {
      await _apiClient.dio.delete(
        ApiEndpoints.adminOrder(id),
        options: _auth(token),
      );
    } on DioException catch (err) {
      throw Exception(_parseValidationError(err, 'No s\'ha pogut eliminar la comanda'));
    }
  }

  String _formatDate(DateTime dt) {
    final String y = dt.year.toString().padLeft(4, '0');
    final String m = dt.month.toString().padLeft(2, '0');
    final String d = dt.day.toString().padLeft(2, '0');
    return '$y-$m-$d';
  }

  String _parseValidationError(DioException err, String fallback) {
    final dynamic data = err.response?.data;
    if (data is Map<String, dynamic>) {
      if (data['message'] is String && (data['message'] as String).isNotEmpty) {
        return data['message'] as String;
      }
      if (data['errors'] is Map<String, dynamic>) {
        final Map<String, dynamic> errors = data['errors'] as Map<String, dynamic>;
        for (final dynamic value in errors.values) {
          if (value is List && value.isNotEmpty) return value.first.toString();
        }
      }
    }
    return fallback;
  }
}
