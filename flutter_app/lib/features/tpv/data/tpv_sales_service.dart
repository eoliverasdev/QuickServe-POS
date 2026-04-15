import 'package:dio/dio.dart';

import '../../../core/network/api_client.dart';
import '../../../core/network/api_endpoints.dart';
import '../../auth/data/auth_service.dart';
import '../domain/tpv_models.dart';

class TpvSalesService {
  TpvSalesService(this._apiClient, this._authService);

  final ApiClient _apiClient;
  final AuthService _authService;

  Future<List<TpvWorker>> fetchWorkers() async {
    final String token = await _requireToken();

    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.workers,
      options: Options(headers: <String, dynamic>{'Authorization': 'Bearer $token'}),
    );

    final dynamic data = response.data;
    if (data is! Map<String, dynamic> || data['workers'] is! List) {
      throw Exception('Respuesta de trabajadores invalida');
    }

    return (data['workers'] as List<dynamic>)
        .whereType<Map<String, dynamic>>()
        .map((Map<String, dynamic> w) => TpvWorker(
              id: (w['id'] as num).toInt(),
              name: (w['name'] ?? '').toString(),
            ))
        .toList();
  }

  Future<void> createOrder({
    required int workerId,
    required String paymentMethod,
    required List<CartItem> cartItems,
    required double totalPrice,
    bool isPreorder = false,
    String? pickupTime,
    String? customerName,
  }) async {
    final String token = await _requireToken();

    final List<Map<String, dynamic>> cart = cartItems
        .map((CartItem item) => <String, dynamic>{
              'id': item.product.id,
              'name': item.product.name,
              'price': item.product.price,
              'quantity': item.quantity,
            })
        .toList();

    await _apiClient.dio.post(
      ApiEndpoints.orders,
      data: <String, dynamic>{
        'worker_id': workerId,
        'payment_method': paymentMethod,
        'total_price': totalPrice,
        'cart': cart,
        'is_preorder': isPreorder,
        'pickup_time': pickupTime,
        'customer_name': customerName,
      },
      options: Options(headers: <String, dynamic>{'Authorization': 'Bearer $token'}),
    );
  }

  Future<List<TpvPreorder>> fetchPendingPreorders() async {
    final String token = await _requireToken();
    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.pendingOrders,
      options: Options(headers: <String, dynamic>{'Authorization': 'Bearer $token'}),
    );

    final dynamic data = response.data;
    if (data is! Map<String, dynamic> || data['orders'] is! List) {
      throw Exception('Respuesta de encarrecs invalida');
    }

    return (data['orders'] as List<dynamic>).whereType<Map<String, dynamic>>().map((Map<String, dynamic> o) {
      final List<dynamic> items = (o['items'] as List<dynamic>? ?? <dynamic>[]);
      return TpvPreorder(
        id: (o['id'] as num?)?.toInt() ?? 0,
        pickupNumber: (o['pickup_number'] as num?)?.toInt(),
        customerName: o['customer_name']?.toString(),
        pickupTime: o['pickup_time']?.toString(),
        totalPrice: (o['total_price'] as num?)?.toDouble() ?? 0,
        itemsCount: items.length,
      );
    }).toList();
  }

  Future<String> _requireToken() async {
    final String? token = await _authService.getStoredToken();
    if (token == null || token.isEmpty) {
      throw Exception('Token no disponible');
    }
    return token;
  }
}
