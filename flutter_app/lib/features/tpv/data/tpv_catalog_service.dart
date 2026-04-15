import 'package:dio/dio.dart';

import '../../../core/network/api_client.dart';
import '../../../core/network/api_endpoints.dart';
import '../../auth/data/auth_service.dart';
import '../domain/tpv_models.dart';

class TpvCatalogService {
  TpvCatalogService(this._apiClient, this._authService);

  final ApiClient _apiClient;
  final AuthService _authService;

  Future<TpvCatalogData> fetchCatalog() async {
    final String? token = await _authService.getStoredToken();
    if (token == null || token.isEmpty) {
      throw Exception('No hay token para cargar el catalogo');
    }

    final Response<dynamic> response = await _apiClient.dio.get(
      ApiEndpoints.catalog,
      options: Options(
        headers: <String, dynamic>{
          'Authorization': 'Bearer $token',
        },
      ),
    );

    final dynamic data = response.data;
    if (data is! Map<String, dynamic>) {
      throw Exception('Respuesta de catalogo invalida');
    }

    final dynamic categoriesRaw = data['categories'];
    final dynamic productsRaw = data['products'];
    if (categoriesRaw is! List || productsRaw is! List) {
      throw Exception('Catalogo incompleto');
    }

    final List<TpvCategory> categories = <TpvCategory>[
      TpvCategory(id: 'all', name: 'Tots'),
      ...categoriesRaw.whereType<Map<String, dynamic>>().map(
            (Map<String, dynamic> c) => TpvCategory(
              id: (c['id'] ?? '').toString(),
              name: (c['name'] ?? '').toString(),
            ),
          ),
    ];

    final List<TpvProduct> products = productsRaw.whereType<Map<String, dynamic>>().map((Map<String, dynamic> p) {
      final List<String> categoryIds = (p['category_ids'] as List<dynamic>? ?? <dynamic>[])
          .map((dynamic id) => id.toString())
          .toList();

      return TpvProduct(
        id: (p['id'] as num?)?.toInt() ?? 0,
        name: (p['name'] ?? '').toString(),
        price: (p['price'] as num?)?.toDouble() ?? 0,
        stock: (p['stock'] as num?)?.toInt(),
        imageUrl: p['image_path']?.toString(),
        categoryIds: categoryIds,
      );
    }).toList();

    return TpvCatalogData(categories: categories, products: products);
  }
}
