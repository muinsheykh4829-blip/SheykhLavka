import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/order.dart';
import '../services/api_service.dart';

class OrderRepository {
  static const String _ordersKey = 'cached_orders';

  /// Загрузить заказы из API базы данных
  static Future<List<Order>> loadOrders() async {
    try {
      print('🔧 Начинаем загрузку заказов...');

      // Проверяем, авторизован ли пользователь
      final isAuth = await ApiService.isAuthenticated();
      if (!isAuth) {
        print('❌ Пользователь не авторизован');
        return [];
      }

      // Загружаем заказы из API
      print('🔧 API URL: ${ApiService.baseUrl}');
      final response = await ApiService.getOrders();
      print('📥 Получен ответ от API: $response');

      if (response['success'] == true && response['orders'] != null) {
        final ordersData = response['orders'] as List<dynamic>;
        print('📦 Найдено заказов в API: ${ordersData.length}');

        final orders = <Order>[];
        for (var orderData in ordersData) {
          try {
            final order = Order.fromJson(orderData);
            orders.add(order);
            print('✅ Заказ ${order.id} успешно преобразован');
          } catch (e) {
            print('❌ Ошибка преобразования заказа: $e');
            print('📦 Данные заказа: $orderData');
          }
        }

        return orders;
      } else {
        print(
            '❌ API вернул ошибку: ${response['message'] ?? 'Неизвестная ошибка'}');
        return [];
      }
    } catch (e) {
      print('❌ Ошибка загрузки заказов из API: $e');
      rethrow;
    }
  }

  /// Загрузить заказы из локального хранилища
  static Future<List<Order>> loadLocalOrders() async {
    final prefs = await SharedPreferences.getInstance();
    final list = prefs.getStringList(_ordersKey) ?? [];

    final orders = <Order>[];
    for (var jsonString in list) {
      try {
        final orderData = json.decode(jsonString);
        final order = Order.fromJson(orderData);
        orders.add(order);
      } catch (e) {
        print('❌ Ошибка загрузки локального заказа: $e');
      }
    }

    return orders;
  }

  /// Сохранить заказы в локальное хранилище
  static Future<void> _saveLocalOrders(List<Order> orders) async {
    final prefs = await SharedPreferences.getInstance();

    final jsonList = <String>[];
    for (var order in orders) {
      try {
        final orderMap = {
          'id': order.id,
          'order_number': order.orderNumber,
          'user_id': order.userId,
          'status': order.status,
          'subtotal': order.subtotal,
          'delivery_fee': order.deliveryFee,
          'discount': order.discount,
          'total': order.total,
          'payment_method': order.paymentMethod,
          'payment_status': order.paymentStatus,
          'delivery_address': order.deliveryAddress,
          'delivery_phone': order.deliveryPhone,
          'delivery_name': order.deliveryName,
          'delivery_time': order.deliveryTime,
          'delivery_type': order.deliveryType,
          'comment': order.comment,
          'created_at': order.createdAt.toIso8601String(),
          'items': order.items
              .map((item) => {
                    'id': item.id,
                    'product_id': item.productId,
                    'quantity': item.quantity,
                    'price': item.price,
                    'total': item.total,
                    'product': item.product,
                  })
              .toList(),
        };
        jsonList.add(json.encode(orderMap));
      } catch (e) {
        print('❌ Ошибка сохранения заказа ${order.id}: $e');
      }
    }

    await prefs.setStringList(_ordersKey, jsonList);
  }

  /// Синхронизировать локальные заказы с API
  static Future<void> syncLocalOrders() async {
    try {
      final apiOrders = await loadOrders();
      await _saveLocalOrders(apiOrders);
    } catch (e) {
      print('❌ Ошибка синхронизации заказов: $e');
    }
  }

  /// Добавить новый заказ в локальное хранилище
  static Future<void> addLocalOrder(Order order) async {
    final prefs = await SharedPreferences.getInstance();
    final list = prefs.getStringList(_ordersKey) ?? [];

    final orderMap = {
      'id': order.id,
      'order_number': order.orderNumber,
      'user_id': order.userId,
      'status': order.status,
      'subtotal': order.subtotal,
      'delivery_fee': order.deliveryFee,
      'discount': order.discount,
      'total': order.total,
      'payment_method': order.paymentMethod,
      'payment_status': order.paymentStatus,
      'delivery_address': order.deliveryAddress,
      'delivery_phone': order.deliveryPhone,
      'delivery_name': order.deliveryName,
      'delivery_time': order.deliveryTime,
      'delivery_type': order.deliveryType,
      'comment': order.comment,
      'created_at': order.createdAt.toIso8601String(),
      'items': order.items
          .map((item) => {
                'id': item.id,
                'product_id': item.productId,
                'quantity': item.quantity,
                'price': item.price,
                'total': item.total,
                'product': item.product,
              })
          .toList(),
    };

    list.insert(0, json.encode(orderMap));
    await prefs.setStringList(_ordersKey, list);
  }

  /// Очистить локальное хранилище заказов
  static Future<void> clearLocalOrders() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_ordersKey);
  }

  /// Удалить заказ из локального хранилища
  static Future<void> removeLocalOrder(int orderId) async {
    final prefs = await SharedPreferences.getInstance();
    final list = prefs.getStringList(_ordersKey) ?? [];

    final filtered = list.where((jsonString) {
      try {
        final orderData = json.decode(jsonString);
        return orderData['id'] != orderId;
      } catch (e) {
        return false;
      }
    }).toList();

    await prefs.setStringList(_ordersKey, filtered);
  }
}
