class TpvCategory {
  TpvCategory({required this.id, required this.name});

  final String id;
  final String name;
}

class TpvProduct {
  TpvProduct({
    required this.id,
    required this.name,
    required this.price,
    required this.categoryIds,
    this.stock,
    this.imageUrl,
  });

  final int id;
  final String name;
  final double price;
  final List<String> categoryIds;
  final int? stock;
  final String? imageUrl;
}

class CartItem {
  CartItem({
    required this.product,
    required this.quantity,
  });

  final TpvProduct product;
  final int quantity;

  double get lineTotal => product.price * quantity;

  CartItem copyWith({int? quantity}) {
    return CartItem(
      product: product,
      quantity: quantity ?? this.quantity,
    );
  }
}

class TpvCatalogData {
  TpvCatalogData({
    required this.categories,
    required this.products,
  });

  final List<TpvCategory> categories;
  final List<TpvProduct> products;
}

class TpvWorker {
  TpvWorker({
    required this.id,
    required this.name,
  });

  final int id;
  final String name;
}

class TpvPreorder {
  TpvPreorder({
    required this.id,
    required this.pickupNumber,
    required this.customerName,
    required this.pickupTime,
    required this.totalPrice,
    required this.itemsCount,
  });

  final int id;
  final int? pickupNumber;
  final String? customerName;
  final String? pickupTime;
  final double totalPrice;
  final int itemsCount;
}
