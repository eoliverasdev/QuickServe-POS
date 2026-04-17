class ApiEndpoints {
  // Health
  static const String ping = '/ping';

  // Auth
  static const String authLogin = '/auth/login';
  static const String authMe = '/auth/me';
  static const String authLogout = '/auth/logout';

  // TPV
  static const String catalog = '/catalog';
  static const String workers = '/workers';
  static const String orders = '/orders';
  static const String pendingOrders = '/orders/pending';

  static String orderCharge(int orderId) => '/orders/$orderId/charge';
  static String orderDetails(int orderId) => '/orders/$orderId/details';
  static String orderCancel(int orderId) => '/orders/$orderId/cancel';

  // Admin
  static const String adminVerifyPin = '/admin/verify-pin';
  static const String adminDashboard = '/admin/dashboard';
}
