class AdminKpi {
  const AdminKpi({
    required this.totalToday,
    required this.ordersToday,
    required this.cashToday,
    required this.cardToday,
    required this.ticketAvg,
    required this.totalLast30d,
    this.bestWorker,
  });

  final double totalToday;
  final int ordersToday;
  final double cashToday;
  final double cardToday;
  final double ticketAvg;
  final double totalLast30d;
  final String? bestWorker;

  factory AdminKpi.fromJson(Map<String, dynamic> json) {
    return AdminKpi(
      totalToday: _asDouble(json['total_today']),
      ordersToday: (json['orders_today'] as num?)?.toInt() ?? 0,
      cashToday: _asDouble(json['cash_today']),
      cardToday: _asDouble(json['card_today']),
      ticketAvg: _asDouble(json['ticket_avg']),
      totalLast30d: _asDouble(json['total_last_30d']),
      bestWorker: json['best_worker']?.toString(),
    );
  }
}

class AdminCaixaSummary {
  const AdminCaixaSummary({
    required this.ivaPercent,
    required this.baseImposable,
    required this.ivaQuota,
    required this.totalBrut,
  });

  final int ivaPercent;
  final double baseImposable;
  final double ivaQuota;
  final double totalBrut;

  factory AdminCaixaSummary.fromJson(Map<String, dynamic> json) {
    return AdminCaixaSummary(
      ivaPercent: (json['iva_percent'] as num?)?.toInt() ?? 21,
      baseImposable: _asDouble(json['base_imposable']),
      ivaQuota: _asDouble(json['iva_quota']),
      totalBrut: _asDouble(json['total_brut']),
    );
  }
}

class AdminTopProduct {
  const AdminTopProduct({
    required this.productId,
    required this.name,
    required this.totalSold,
  });

  final int productId;
  final String name;
  final double totalSold;

  factory AdminTopProduct.fromJson(Map<String, dynamic> json) {
    return AdminTopProduct(
      productId: (json['product_id'] as num?)?.toInt() ?? 0,
      name: json['name']?.toString() ?? '',
      totalSold: _asDouble(json['total_venuts']),
    );
  }
}

class AdminRevenueDay {
  const AdminRevenueDay({
    required this.label,
    required this.date,
    required this.total,
  });

  final String label;
  final String date;
  final double total;

  factory AdminRevenueDay.fromJson(Map<String, dynamic> json) {
    return AdminRevenueDay(
      label: json['label']?.toString() ?? '',
      date: json['date']?.toString() ?? '',
      total: _asDouble(json['total']),
    );
  }
}

class AdminPeakHour {
  const AdminPeakHour({required this.hour, required this.count});

  final int hour;
  final int count;

  factory AdminPeakHour.fromJson(Map<String, dynamic> json) {
    return AdminPeakHour(
      hour: (json['hour'] as num?)?.toInt() ?? 0,
      count: (json['count'] as num?)?.toInt() ?? 0,
    );
  }
}

class AdminPaymentSplit {
  const AdminPaymentSplit({required this.cash, required this.card});

  final double cash;
  final double card;

  double get total => cash + card;

  factory AdminPaymentSplit.fromJson(Map<String, dynamic> json) {
    return AdminPaymentSplit(
      cash: _asDouble(json['cash']),
      card: _asDouble(json['card']),
    );
  }
}

class AdminDayTop {
  const AdminDayTop({
    required this.dow,
    required this.name,
    required this.shortName,
    required this.items,
  });

  final int dow;
  final String name;
  final String shortName;
  final List<AdminTopProduct> items;

  factory AdminDayTop.fromJson(Map<String, dynamic> json) {
    final List<dynamic> rawItems = (json['items'] as List<dynamic>? ?? <dynamic>[]);
    return AdminDayTop(
      dow: (json['dow'] as num?)?.toInt() ?? 0,
      name: json['name']?.toString() ?? '',
      shortName: json['short']?.toString() ?? '',
      items: rawItems
          .whereType<Map<String, dynamic>>()
          .map(AdminTopProduct.fromJson)
          .toList(),
    );
  }
}

class AdminDashboardData {
  const AdminDashboardData({
    required this.kpi,
    required this.caixa,
    required this.topProducts,
    required this.revenueWeek,
    required this.peakHours,
    required this.paymentMonth,
    required this.topPerDay,
    required this.currentDow,
  });

  final AdminKpi kpi;
  final AdminCaixaSummary caixa;
  final List<AdminTopProduct> topProducts;
  final List<AdminRevenueDay> revenueWeek;
  final List<AdminPeakHour> peakHours;
  final AdminPaymentSplit paymentMonth;
  final List<AdminDayTop> topPerDay;
  final int currentDow;

  factory AdminDashboardData.fromJson(Map<String, dynamic> json) {
    return AdminDashboardData(
      kpi: AdminKpi.fromJson(json['kpi'] as Map<String, dynamic>? ?? <String, dynamic>{}),
      caixa: AdminCaixaSummary.fromJson(json['caixa'] as Map<String, dynamic>? ?? <String, dynamic>{}),
      topProducts: ((json['top_products'] as List<dynamic>?) ?? <dynamic>[])
          .whereType<Map<String, dynamic>>()
          .map(AdminTopProduct.fromJson)
          .toList(),
      revenueWeek: ((json['revenue_week'] as List<dynamic>?) ?? <dynamic>[])
          .whereType<Map<String, dynamic>>()
          .map(AdminRevenueDay.fromJson)
          .toList(),
      peakHours: ((json['peak_hours'] as List<dynamic>?) ?? <dynamic>[])
          .whereType<Map<String, dynamic>>()
          .map(AdminPeakHour.fromJson)
          .toList(),
      paymentMonth: AdminPaymentSplit.fromJson(json['payment_month'] as Map<String, dynamic>? ?? <String, dynamic>{}),
      topPerDay: ((json['top_per_day'] as List<dynamic>?) ?? <dynamic>[])
          .whereType<Map<String, dynamic>>()
          .map(AdminDayTop.fromJson)
          .toList(),
      currentDow: (json['current_dow'] as num?)?.toInt() ?? 0,
    );
  }
}

class AdminCategory {
  const AdminCategory({
    required this.id,
    required this.name,
    required this.productsCount,
    this.color,
  });

  final int id;
  final String name;
  final String? color;
  final int productsCount;

  factory AdminCategory.fromJson(Map<String, dynamic> json) {
    return AdminCategory(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name']?.toString() ?? '',
      color: json['color']?.toString(),
      productsCount: (json['products_count'] as num?)?.toInt() ?? 0,
    );
  }
}

class AdminProduct {
  const AdminProduct({
    required this.id,
    required this.name,
    required this.price,
    required this.stock,
    required this.isGlutenFree,
    required this.active,
    this.description,
    this.imagePath,
    this.categoryId,
    this.categoryName,
    this.categoryColor,
  });

  final int id;
  final String name;
  final double price;
  final int stock;
  final bool isGlutenFree;
  final bool active;
  final String? description;
  final String? imagePath;
  final int? categoryId;
  final String? categoryName;
  final String? categoryColor;

  factory AdminProduct.fromJson(Map<String, dynamic> json) {
    return AdminProduct(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name']?.toString() ?? '',
      price: _asDouble(json['price']),
      stock: (json['stock'] as num?)?.toInt() ?? 0,
      isGlutenFree: (json['is_gluten_free'] as bool?) ?? false,
      active: (json['active'] as bool?) ?? true,
      description: json['description']?.toString(),
      imagePath: json['image_path']?.toString(),
      categoryId: (json['category_id'] as num?)?.toInt(),
      categoryName: json['category_name']?.toString(),
      categoryColor: json['category_color']?.toString(),
    );
  }
}

class AdminWorker {
  const AdminWorker({
    required this.id,
    required this.name,
    required this.hasPin,
    required this.active,
    required this.ordersCount,
    this.pin,
  });

  final int id;
  final String name;
  final bool hasPin;
  final bool active;
  final int ordersCount;
  final String? pin;

  factory AdminWorker.fromJson(Map<String, dynamic> json) {
    return AdminWorker(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name']?.toString() ?? '',
      hasPin: (json['has_pin'] as bool?) ?? false,
      active: (json['active'] as bool?) ?? true,
      ordersCount: (json['orders_count'] as num?)?.toInt() ?? 0,
      pin: json['pin']?.toString(),
    );
  }
}

class AdminOrderSummary {
  const AdminOrderSummary({
    required this.id,
    required this.totalPrice,
    required this.status,
    required this.isPreorder,
    required this.itemsCount,
    this.paymentMethod,
    this.pickupNumber,
    this.pickupTime,
    this.customerName,
    this.fiscalFullNumber,
    this.workerId,
    this.workerName,
    this.createdAt,
  });

  final int id;
  final double totalPrice;
  final String? paymentMethod;
  final String status;
  final bool isPreorder;
  final int itemsCount;
  final String? pickupNumber;
  final String? pickupTime;
  final String? customerName;
  final String? fiscalFullNumber;
  final int? workerId;
  final String? workerName;
  final DateTime? createdAt;

  factory AdminOrderSummary.fromJson(Map<String, dynamic> json) {
    return AdminOrderSummary(
      id: (json['id'] as num?)?.toInt() ?? 0,
      totalPrice: _asDouble(json['total_price']),
      paymentMethod: json['payment_method']?.toString(),
      status: json['status']?.toString() ?? '',
      isPreorder: (json['is_preorder'] as bool?) ?? false,
      itemsCount: (json['items_count'] as num?)?.toInt() ?? 0,
      pickupNumber: json['pickup_number']?.toString(),
      pickupTime: json['pickup_time']?.toString(),
      customerName: json['customer_name']?.toString(),
      fiscalFullNumber: json['fiscal_full_number']?.toString(),
      workerId: (json['worker_id'] as num?)?.toInt(),
      workerName: json['worker_name']?.toString(),
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at'].toString()) : null,
    );
  }
}

class AdminOrderItem {
  const AdminOrderItem({
    required this.id,
    required this.name,
    required this.quantity,
    required this.price,
    required this.subtotal,
  });

  final int id;
  final String name;
  final int quantity;
  final double price;
  final double subtotal;

  factory AdminOrderItem.fromJson(Map<String, dynamic> json) {
    return AdminOrderItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name']?.toString() ?? '',
      quantity: (json['quantity'] as num?)?.toInt() ?? 0,
      price: _asDouble(json['price']),
      subtotal: _asDouble(json['subtotal']),
    );
  }
}

class AdminOrderDetail {
  const AdminOrderDetail({required this.summary, required this.items});

  final AdminOrderSummary summary;
  final List<AdminOrderItem> items;

  factory AdminOrderDetail.fromJson(Map<String, dynamic> json) {
    return AdminOrderDetail(
      summary: AdminOrderSummary.fromJson(json),
      items: (json['items'] as List<dynamic>? ?? <dynamic>[])
          .whereType<Map<String, dynamic>>()
          .map(AdminOrderItem.fromJson)
          .toList(),
    );
  }
}

class AdminOrdersPage {
  const AdminOrdersPage({
    required this.orders,
    required this.currentPage,
    required this.lastPage,
    required this.total,
    required this.perPage,
  });

  final List<AdminOrderSummary> orders;
  final int currentPage;
  final int lastPage;
  final int total;
  final int perPage;

  factory AdminOrdersPage.fromJson(Map<String, dynamic> json) {
    final Map<String, dynamic> meta =
        (json['meta'] as Map<String, dynamic>?) ?? const <String, dynamic>{};
    return AdminOrdersPage(
      orders: (json['orders'] as List<dynamic>? ?? <dynamic>[])
          .whereType<Map<String, dynamic>>()
          .map(AdminOrderSummary.fromJson)
          .toList(),
      currentPage: (meta['current_page'] as num?)?.toInt() ?? 1,
      lastPage: (meta['last_page'] as num?)?.toInt() ?? 1,
      total: (meta['total'] as num?)?.toInt() ?? 0,
      perPage: (meta['per_page'] as num?)?.toInt() ?? 20,
    );
  }
}

double _asDouble(dynamic value) {
  if (value is num) return value.toDouble();
  if (value is String) return double.tryParse(value) ?? 0;
  return 0;
}
