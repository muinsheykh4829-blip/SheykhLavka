import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/order.dart';

class ApiService {
  static const int _timeoutSeconds = 30;

  // Метод для очистки ответа от "---" префикса
  static String _cleanResponse(String responseBody) {
    if (responseBody.startsWith('---')) {
      int firstBrace = responseBody.indexOf('{');
      if (firstBrace != -1) {
        return responseBody.substring(firstBrace);
      }
    }
    return responseBody;
  }

  // Безопасное декодирование JSON
  static Map<String, dynamic> _safeJsonDecode(String responseBody) {
    try {
      String cleanedBody = _cleanResponse(responseBody);
      return jsonDecode(cleanedBody) as Map<String, dynamic>;
    } catch (e) {
      print('Ошибка парсинга JSON: $e');
      print('Ответ сервера: $responseBody');
      throw const FormatException('Некорректный формат ответа сервера');
    }
  }

  static Future<Map<String, dynamic>> login(
      String login, String password) async {
    try {
      print('Попытка входа: login=$login');
      print('API URL: ${ApiConfig.baseUrl}${ApiConfig.courierLoginEndpoint}');

      final response = await http
          .post(
            Uri.parse('${ApiConfig.baseUrl}${ApiConfig.courierLoginEndpoint}'),
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: jsonEncode({
              'login': login,
              'password': password,
            }),
          )
          .timeout(const Duration(seconds: _timeoutSeconds));

      print('Статус ответа: ${response.statusCode}');
      print('Тело ответа (сырое): ${response.body}');

      final data = _safeJsonDecode(response.body);
      print('Декодированные данные: $data');

      if (response.statusCode == 200 && data['success'] == true) {
        print('Успешный вход!');
        return data;
      } else {
        print('Ошибка входа: ${data['message']}');
        throw Exception(data['message'] ?? 'Ошибка авторизации');
      }
    } catch (e) {
      print('Ошибка входа: $e');
      rethrow;
    }
  }

  static Future<List<CourierOrder>> getOrders({
    required String token,
    String status = 'ready',
  }) async {
    try {
      final response = await http.get(
        Uri.parse(
            '${ApiConfig.baseUrl}${ApiConfig.courierOrdersEndpoint}?status=$status'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: _timeoutSeconds));

      final data = _safeJsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        List<CourierOrder> orders = [];
        if (data['orders'] != null) {
          for (var orderData in data['orders']) {
            try {
              orders.add(CourierOrder.fromJson(orderData));
            } catch (e) {
              print('Ошибка обработки заказа #${orderData['id']}: $e');
            }
          }
        }
        return orders;
      } else {
        throw Exception(data['message'] ?? 'Ошибка получения заказов');
      }
    } catch (e) {
      print('Ошибка получения заказов: $e');
      rethrow;
    }
  }

  static Future<Map<String, dynamic>> takeOrder({
    required String token,
    required int orderId,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(
            '${ApiConfig.baseUrl}${ApiConfig.takeOrderEndpoint(orderId)}'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: _timeoutSeconds));

      final data = _safeJsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return data;
      } else {
        throw Exception(data['message'] ?? 'Ошибка при взятии заказа');
      }
    } catch (e) {
      print('Ошибка взятия заказа: $e');
      rethrow;
    }
  }

  static Future<Map<String, dynamic>> completeOrder({
    required String token,
    required int orderId,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(
            '${ApiConfig.baseUrl}${ApiConfig.completeOrderEndpoint(orderId)}'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: _timeoutSeconds));

      final data = _safeJsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return data;
      } else {
        throw Exception(data['message'] ?? 'Ошибка при завершении доставки');
      }
    } catch (e) {
      print('Ошибка завершения доставки: $e');
      rethrow;
    }
  }

  static Future<Map<String, dynamic>> logout({required String token}) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.courierLogoutEndpoint}'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: _timeoutSeconds));

      final data = _safeJsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return data;
      } else {
        throw Exception(data['message'] ?? 'Ошибка при выходе');
      }
    } catch (e) {
      print('Ошибка выхода: $e');
      rethrow;
    }
  }
}
