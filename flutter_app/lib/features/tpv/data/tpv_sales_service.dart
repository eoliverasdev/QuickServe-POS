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

  Future<Map<String, dynamic>> createOrder({
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

    final Response<dynamic> response = await _apiClient.dio.post(
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

    final dynamic data = response.data;
    if (data is Map<String, dynamic>) {
      return data;
    }
    return <String, dynamic>{};
  }

  Future<List<TpvPreorder>> fetchPendingPreorders() async {
    final String token = await _requireToken();
    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.pendingOrders,
      options: Options(headers: <String, dynamic>{'Authorization': 'Bearer $token'}),
    );

    final dynamic data = response.data;
    if (data is! Map<String, dynamic>) {
      throw Exception('Respuesta de encarrecs invalida');
    }

    final List<Map<String, dynamic>> orderMaps = _normalizeOrderList(data['orders']);
    return orderMaps.map((Map<String, dynamic> o) {
      final List<dynamic> items = (o['items'] as List<dynamic>? ?? <dynamic>[]);
      return TpvPreorder(
        id: (o['id'] as num?)?.toInt() ?? 0,
        pickupNumber: (o['pickup_number'] as num?)?.toInt(),
        customerName: o['customer_name']?.toString(),
        pickupTime: o['pickup_time']?.toString(),
        totalPrice: _parseDouble(o['total_price']),
        itemsCount: items.length,
      );
    }).toList();
  }

  Future<void> chargePreorder({
    required int orderId,
    required String paymentMethod,
    required int workerId,
    int bagCount = 0,
    int? bagProductId,
  }) async {
    final String token = await _requireToken();
    await _apiClient.dio.post(
      ApiEndpoints.orderCharge(orderId),
      data: <String, dynamic>{
        'payment_method': paymentMethod,
        'worker_id': workerId,
        'bag_count': bagCount,
        'bag_product_id': bagProductId,
      },
      options: Options(headers: <String, dynamic>{'Authorization': 'Bearer $token'}),
    );
  }

  Future<void> cancelPreorder({required int orderId}) async {
    final String token = await _requireToken();
    await _apiClient.dio.post(
      ApiEndpoints.orderCancel(orderId),
      options: Options(headers: <String, dynamic>{'Authorization': 'Bearer $token'}),
    );
  }

  Future<TpvOrderDetail> fetchOrderDetails({required int orderId}) async {
    final String token = await _requireToken();
    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.orderDetails(orderId),
      options: Options(headers: <String, dynamic>{'Authorization': 'Bearer $token'}),
    );

    final dynamic data = response.data;
    if (data is! Map<String, dynamic> || data['order'] is! Map<String, dynamic>) {
      throw Exception('Resposta de detall invalida');
    }

    final Map<String, dynamic> order = data['order'] as Map<String, dynamic>;
    final List<TpvOrderDetailItem> items = (order['items'] as List<dynamic>? ?? <dynamic>[])
        .whereType<Map<String, dynamic>>()
        .map((Map<String, dynamic> item) {
      final Map<String, dynamic> product = item['product'] as Map<String, dynamic>? ?? <String, dynamic>{};
      return TpvOrderDetailItem(
        productId: (item['product_id'] as num?)?.toInt() ?? 0,
        productName: product['name']?.toString() ?? 'Producte',
        quantity: (item['quantity'] as num?)?.toInt() ?? 0,
        priceAtSale: _parseDouble(item['price_at_sale']),
        notes: item['notes']?.toString(),
        imagePath: product['image_path']?.toString(),
      );
    }).toList();

    return TpvOrderDetail(
      id: (order['id'] as num?)?.toInt() ?? orderId,
      pickupNumber: (order['pickup_number'] as num?)?.toInt(),
      customerName: order['customer_name']?.toString(),
      pickupTime: order['pickup_time']?.toString(),
      totalPrice: _parseDouble(order['total_price']),
      items: items,
      paymentMethod: order['payment_method']?.toString() ?? '',
      createdAt: DateTime.tryParse(order['created_at']?.toString() ?? ''),
      fiscalFullNumber: order['fiscal_full_number']?.toString(),
    );
  }

  List<Map<String, dynamic>> _normalizeOrderList(dynamic rawOrders) {
    if (rawOrders is List<dynamic>) {
      return rawOrders.whereType<Map<String, dynamic>>().toList();
    }

    if (rawOrders is Map<String, dynamic>) {
      return rawOrders.values.whereType<Map<String, dynamic>>().toList();
    }

    throw Exception('Respuesta de encarrecs invalida');
  }

  double _parseDouble(dynamic value) {
    if (value is num) {
      return value.toDouble();
    }
    if (value is String) {
      return double.tryParse(value) ?? 0;
    }
    return 0;
  }

  Future<String> _requireToken() async {
    final String? token = await _authService.getStoredToken();
    if (token == null || token.isEmpty) {
      throw Exception('Token no disponible');
    }
    return token;
  }
}
