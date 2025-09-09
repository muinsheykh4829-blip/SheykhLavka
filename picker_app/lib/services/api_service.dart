import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';
import '../models/order.dart';

class ApiService {
  static Map<String, String> _getHeaders({String? token}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (token != null) {
      headers['Authorization'] = 'Bearer $token';
    }

    return headers;
  }

  // Функция для очистки ответа от лишних символов
  static String _cleanResponse(String response) {
    // Удаляем префикс "---" если он есть
    if (response.startsWith('---')) {
      response = response.substring(3);
    }

    // Удаляем пробелы и переносы строк в начале
    response = response.trimLeft();

    return response;
  }

  // Безопасное декодирование JSON
  static Map<String, dynamic> _safeJsonDecode(String response) {
    try {
      final cleanedResponse = _cleanResponse(response);
      print('Cleaned response: $cleanedResponse');
      return jsonDecode(cleanedResponse);
    } catch (e) {
      print('JSON decode error: $e');
      print('Raw response: $response');
      return {
        'success': false,
        'message': 'Ошибка обработки ответа сервера',
      };
    }
  }

  // Авторизация сборщика
  static Future<Map<String, dynamic>> login(
      String login, String password) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/login'),
        headers: _getHeaders(),
        body: jsonEncode({
          'login': login,
          'password': password,
        }),
      );

      print('Login response status: ${response.statusCode}');
      print('Login response body: ${response.body}');

      return _safeJsonDecode(response.body);
    } catch (e) {
      print('Login error: $e');
      return {
        'success': false,
        'message': 'Ошибка сети: $e',
      };
    }
  }

  // Получить заказы
  static Future<Map<String, dynamic>> getOrders(
      {String status = 'accepted'}) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('picker_token');

      if (token == null) {
        return {'success': false, 'message': 'Токен не найден'};
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}/orders?status=$status'),
        headers: _getHeaders(token: token),
      );

      final data = _safeJsonDecode(response.body);

      if (data['success'] == true) {
        final orders = (data['orders'] as List)
            .map((json) => Order.fromJson(json))
            .toList();

        return {
          'success': true,
          'orders': orders,
        };
      }

      return data;
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка загрузки заказов: $e',
      };
    }
  }

  // Получить детали заказа
  static Future<Map<String, dynamic>> getOrderDetails(int orderId) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('picker_token');

      if (token == null) {
        return {'success': false, 'message': 'Токен не найден'};
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}/orders/$orderId'),
        headers: _getHeaders(token: token),
      );

      final data = _safeJsonDecode(response.body);

      if (data['success'] == true) {
        return {
          'success': true,
          'order': Order.fromJson(data['order']),
        };
      }

      return data;
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка загрузки заказа: $e',
      };
    }
  }

  // Начать сборку заказа
  static Future<Map<String, dynamic>> startPicking(int orderId) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('picker_token');

      if (token == null) {
        return {'success': false, 'message': 'Токен не найден'};
      }

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/orders/$orderId/start'),
        headers: _getHeaders(token: token),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка начала сборки: $e',
      };
    }
  }

  // Завершить сборку заказа
  static Future<Map<String, dynamic>> completePicking(int orderId) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('picker_token');

      if (token == null) {
        return {'success': false, 'message': 'Токен не найден'};
      }

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/orders/$orderId/complete'),
        headers: _getHeaders(token: token),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка завершения сборки: $e',
      };
    }
  }

  // Получить статистику
  static Future<Map<String, dynamic>> getStatistics() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('picker_token');

      if (token == null) {
        return {'success': false, 'message': 'Токен не найден'};
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}/statistics'),
        headers: _getHeaders(token: token),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка получения статистики: $e',
      };
    }
  }

  // Сохранить токен
  static Future<void> saveToken(
      String token, String login, String pickerName) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('picker_token', token);
    await prefs.setString('picker_login', login);
    await prefs.setString('picker_name', pickerName);
  }

  // Удалить токен (выход)
  static Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('picker_token');
    await prefs.remove('picker_login');
    await prefs.remove('picker_name');
  }

  // Проверить авторизацию
  static Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('picker_token') != null;
  }

  // Взять заказ в работу
  static Future<Map<String, dynamic>> takeOrder(int orderId) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('picker_token');

      if (token == null) {
        return {'success': false, 'message': 'Токен не найден'};
      }

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/orders/$orderId/take'),
        headers: _getHeaders(token: token),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка взятия заказа: $e',
      };
    }
  }

  // Завершить заказ
  static Future<Map<String, dynamic>> completeOrder(int orderId) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('picker_token');

      if (token == null) {
        return {'success': false, 'message': 'Токен не найден'};
      }

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/orders/$orderId/complete'),
        headers: _getHeaders(token: token),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка завершения заказа: $e',
      };
    }
  }

  // Получить сохраненные данные пользователя
  static Future<Map<String, dynamic>?> getSavedUserData() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('picker_token');
      final login = prefs.getString('picker_login');
      final name = prefs.getString('picker_name');

      if (token != null && login != null && name != null) {
        return {
          'token': token,
          'login': login,
          'name': name,
        };
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  // Проверить токен на валидность
  static Future<bool> validateToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('picker_token');

      if (token == null) return false;

      // Простая проверка - пытаемся получить заказы
      final result = await getOrders(status: 'accepted');
      return result['success'] == true;
    } catch (e) {
      return false;
    }
  }
}
