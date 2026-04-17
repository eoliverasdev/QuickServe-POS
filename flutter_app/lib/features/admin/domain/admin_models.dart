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

double _asDouble(dynamic value) {
  if (value is num) return value.toDouble();
  if (value is String) return double.tryParse(value) ?? 0;
  return 0;
}
