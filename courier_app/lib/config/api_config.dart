class ApiConfig {
  static const String baseUrl = 'http://127.0.0.1:8000/api';
  static const String courierLoginEndpoint = '/courier/login';
  static const String courierOrdersEndpoint = '/courier/orders';
  static const String courierLogoutEndpoint = '/courier/logout';

  static String takeOrderEndpoint(int orderId) =>
      '/courier/orders/$orderId/take';
  static String completeOrderEndpoint(int orderId) =>
      '/courier/orders/$orderId/complete';
}
