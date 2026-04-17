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
    this.notes,
  });

  final TpvProduct product;
  final int quantity;
  final String? notes;

  String get cartKey => '${product.id}|${notes ?? ''}';

  double get lineTotal => product.price * quantity;

  CartItem copyWith({int? quantity, String? notes}) {
    return CartItem(
      product: product,
      quantity: quantity ?? this.quantity,
      notes: notes ?? this.notes,
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

class TpvOrderDetail {
  TpvOrderDetail({
    required this.id,
    required this.pickupNumber,
    required this.customerName,
    required this.pickupTime,
    required this.totalPrice,
    required this.items,
    required this.paymentMethod,
    required this.createdAt,
    this.fiscalFullNumber,
  });

  final int id;
  final int? pickupNumber;
  final String? customerName;
  final String? pickupTime;
  final double totalPrice;
  final List<TpvOrderDetailItem> items;
  final String paymentMethod;
  final DateTime? createdAt;
  final String? fiscalFullNumber;
}

class TpvOrderDetailItem {
  TpvOrderDetailItem({
    required this.productId,
    required this.productName,
    required this.quantity,
    required this.priceAtSale,
    this.notes,
    this.imagePath,
  });

  final int productId;
  final String productName;
  final int quantity;
  final double priceAtSale;
  final String? notes;
  final String? imagePath;
}

class ParkedTicket {
  ParkedTicket({
    required this.id,
    required this.createdAt,
    required this.items,
  });

  final String id;
  final DateTime createdAt;
  final List<CartItem> items;
}
