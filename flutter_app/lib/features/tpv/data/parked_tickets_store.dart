import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import '../domain/tpv_models.dart';

class ParkedTicketsStore {
  static const String _key = 'tpv_parked_tickets';

  Future<List<ParkedTicket>> load(List<TpvProduct> products) async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    final String? raw = prefs.getString(_key);
    if (raw == null || raw.isEmpty) return <ParkedTicket>[];

    final dynamic decoded = jsonDecode(raw);
    if (decoded is! List<dynamic>) return <ParkedTicket>[];

    final Map<int, TpvProduct> productById = <int, TpvProduct>{
      for (final TpvProduct product in products) product.id: product,
    };

    return decoded.whereType<Map<String, dynamic>>().map((Map<String, dynamic> map) {
      final List<dynamic> rawItems = map['items'] as List<dynamic>? ?? <dynamic>[];
      final List<CartItem> items = rawItems.whereType<Map<String, dynamic>>().map((Map<String, dynamic> item) {
        final int id = (item['product_id'] as num?)?.toInt() ?? 0;
        final TpvProduct? product = productById[id];
        if (product == null) {
          return CartItem(
            product: TpvProduct(id: id, name: 'Producte #$id', price: _parseDouble(item['price']), categoryIds: <String>[]),
            quantity: (item['quantity'] as num?)?.toInt() ?? 1,
            notes: item['notes']?.toString(),
          );
        }
        return CartItem(
          product: product,
          quantity: (item['quantity'] as num?)?.toInt() ?? 1,
          notes: item['notes']?.toString(),
        );
      }).toList();

      return ParkedTicket(
        id: map['id']?.toString() ?? DateTime.now().millisecondsSinceEpoch.toString(),
        createdAt: DateTime.tryParse(map['created_at']?.toString() ?? '') ?? DateTime.now(),
        items: items,
      );
    }).toList();
  }

  Future<void> save(List<ParkedTicket> tickets) async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    final List<Map<String, dynamic>> payload = tickets.map((ParkedTicket ticket) {
      return <String, dynamic>{
        'id': ticket.id,
        'created_at': ticket.createdAt.toIso8601String(),
        'items': ticket.items.map((CartItem item) {
          return <String, dynamic>{
            'product_id': item.product.id,
            'quantity': item.quantity,
            'notes': item.notes,
            'price': item.product.price,
          };
        }).toList(),
      };
    }).toList();

    await prefs.setString(_key, jsonEncode(payload));
  }

  double _parseDouble(dynamic value) {
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0;
    return 0;
  }
}
