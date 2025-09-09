import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';

class OrderService {
  // Используем единую конфигурацию API
  static String get baseUrl => ApiConfig.currentUrl;

  // Создать новый заказ
  static Future<Map<String, dynamic>> createOrder({
    required String deliveryAddress,
    required String deliveryPhone,
    String? deliveryName,
    String? deliveryTime,
    String paymentMethod = 'cash',
    String? comment,
  }) async {
    try {
      // Получаем токен авторизации
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      if (token == null) {
        return {'success': false, 'message': 'Пользователь не авторизован'};
      }

      // Формируем данные заказа
      final orderData = {
        'delivery_address': deliveryAddress,
        'delivery_phone': deliveryPhone,
        'delivery_name': deliveryName,
        'delivery_time': deliveryTime,
        'payment_method': paymentMethod,
        'comment': comment,
      };

      // Отправляем запрос
      final response = await http.post(
        Uri.parse('$baseUrl/orders'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
        body: jsonEncode(orderData),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        // Заказ создан успешно
        return {
          'success': true,
          'message': responseData['message'],
          'order': responseData['order'],
        };
      } else if (response.statusCode == 422) {
        // Ошибка валидации
        return {
          'success': false,
          'message': responseData['message'],
          'errors': responseData['errors'],
        };
      } else {
        // Другие ошибки
        return {
          'success': false,
          'message': responseData['message'] ?? 'Ошибка создания заказа',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка сети: $e',
      };
    }
  }

  // Получить список заказов пользователя
  static Future<Map<String, dynamic>> getUserOrders() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      if (token == null) {
        return {'success': false, 'message': 'Пользователь не авторизован'};
      }

      final response = await http.get(
        Uri.parse('$baseUrl/orders'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'orders': responseData['orders'],
        };
      } else {
        return {
          'success': false,
          'message': responseData['message'] ?? 'Ошибка получения заказов',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка сети: $e',
      };
    }
  }

  // Отменить заказ
  static Future<Map<String, dynamic>> cancelOrder(int orderId) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      if (token == null) {
        return {'success': false, 'message': 'Пользователь не авторизован'};
      }

      final response = await http.put(
        Uri.parse('$baseUrl/orders/$orderId/cancel'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': responseData['message'],
          'order': responseData['order'],
        };
      } else {
        return {
          'success': false,
          'message': responseData['message'] ?? 'Ошибка отмены заказа',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка сети: $e',
      };
    }
  }
}
