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
}
