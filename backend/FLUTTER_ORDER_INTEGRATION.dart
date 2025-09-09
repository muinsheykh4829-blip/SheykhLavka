// Flutter код для создания заказа через API
// Файл: lib/services/order_service.dart

import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class OrderService {
  static const String baseUrl = 'http://127.0.0.1:8000/api';

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

// =============================================================================
// Пример использования в виджете Flutter
// Файл: lib/screens/checkout_screen.dart

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  _CheckoutScreenState createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _addressController = TextEditingController();
  final _phoneController = TextEditingController();
  final _nameController = TextEditingController();
  final _commentController = TextEditingController();

  String _paymentMethod = 'cash';
  bool _isLoading = false;

  // Оформить заказ
  Future<void> _createOrder() async {
    if (_addressController.text.trim().isEmpty ||
        _phoneController.text.trim().isEmpty) {
      _showError('Заполните адрес и телефон');
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final result = await OrderService.createOrder(
        deliveryAddress: _addressController.text.trim(),
        deliveryPhone: _phoneController.text.trim(),
        deliveryName: _nameController.text.trim().isEmpty
            ? null
            : _nameController.text.trim(),
        paymentMethod: _paymentMethod,
        comment: _commentController.text.trim().isEmpty
            ? null
            : _commentController.text.trim(),
      );

      if (result['success']) {
        // Заказ создан успешно
        _showSuccess('Заказ №${result['order']['order_number']} создан!');

        // Переходим на страницу истории заказов или главную
        Navigator.of(context).pushNamedAndRemoveUntil(
          '/orders',
          (route) => false,
        );
      } else {
        // Ошибка создания заказа
        _showError(result['message']);

        // Показываем ошибки валидации если есть
        if (result['errors'] != null) {
          String errors = '';
          result['errors'].forEach((field, messages) {
            errors += '${messages.join(', ')}\n';
          });
          _showError(errors);
        }
      }
    } catch (e) {
      _showError('Произошла ошибка: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  void _showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.green,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Оформление заказа'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            // Адрес доставки
            TextField(
              controller: _addressController,
              decoration: const InputDecoration(
                labelText: 'Адрес доставки *',
                hintText: 'г. Ташкент, ул. Амира Темура 15, кв. 25',
              ),
              maxLines: 2,
            ),

            const SizedBox(height: 16),

            // Телефон
            TextField(
              controller: _phoneController,
              decoration: const InputDecoration(
                labelText: 'Телефон *',
                hintText: '+998901234567',
              ),
              keyboardType: TextInputType.phone,
            ),

            const SizedBox(height: 16),

            // Имя получателя
            TextField(
              controller: _nameController,
              decoration: const InputDecoration(
                labelText: 'Имя получателя',
                hintText: 'Иван Иванов',
              ),
            ),

            const SizedBox(height: 16),

            // Способ оплаты
            DropdownButtonFormField<String>(
              initialValue: _paymentMethod,
              decoration: const InputDecoration(
                labelText: 'Способ оплаты',
              ),
              items: const [
                DropdownMenuItem(value: 'cash', child: Text('Наличные')),
                DropdownMenuItem(
                    value: 'card', child: Text('Картой при получении')),
                DropdownMenuItem(value: 'online', child: Text('Онлайн оплата')),
              ],
              onChanged: (value) {
                setState(() {
                  _paymentMethod = value!;
                });
              },
            ),

            const SizedBox(height: 16),

            // Комментарий
            TextField(
              controller: _commentController,
              decoration: const InputDecoration(
                labelText: 'Комментарий к заказу',
                hintText: 'Позвонить за 10 минут до доставки',
              ),
              maxLines: 3,
            ),

            const SizedBox(height: 32),

            // Кнопка оформления
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _createOrder,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isLoading
                    ? const CircularProgressIndicator(color: Colors.white)
                    : const Text('Оформить заказ'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _addressController.dispose();
    _phoneController.dispose();
    _nameController.dispose();
    _commentController.dispose();
    super.dispose();
  }
}

// =============================================================================
// Модель заказа для Flutter
// Файл: lib/models/order.dart

class Order {
  final int id;
  final String orderNumber;
  final int userId;
  final String status;
  final int subtotal;
  final int deliveryFee;
  final int discount;
  final int total;
  final String paymentMethod;
  final String paymentStatus;
  final String deliveryAddress;
  final String deliveryPhone;
  final String? deliveryName;
  final String? deliveryTime;
  final String? comment;
  final DateTime createdAt;
  final List<OrderItem> items;

  Order({
    required this.id,
    required this.orderNumber,
    required this.userId,
    required this.status,
    required this.subtotal,
    required this.deliveryFee,
    required this.discount,
    required this.total,
    required this.paymentMethod,
    required this.paymentStatus,
    required this.deliveryAddress,
    required this.deliveryPhone,
    this.deliveryName,
    this.deliveryTime,
    this.comment,
    required this.createdAt,
    required this.items,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'],
      orderNumber: json['order_number'],
      userId: json['user_id'],
      status: json['status'],
      subtotal: json['subtotal'],
      deliveryFee: json['delivery_fee'],
      discount: json['discount'],
      total: json['total'],
      paymentMethod: json['payment_method'],
      paymentStatus: json['payment_status'],
      deliveryAddress: json['delivery_address'],
      deliveryPhone: json['delivery_phone'],
      deliveryName: json['delivery_name'],
      deliveryTime: json['delivery_time'],
      comment: json['comment'],
      createdAt: DateTime.parse(json['created_at']),
      items: (json['items'] as List)
          .map((item) => OrderItem.fromJson(item))
          .toList(),
    );
  }

  // Получить локализованное название статуса
  String get statusName {
    switch (status) {
      case 'pending':
        return 'Ожидает';
      case 'confirmed':
        return 'Подтвержден';
      case 'preparing':
        return 'Готовится';
      case 'ready':
        return 'Готов';
      case 'delivering':
        return 'Доставляется';
      case 'delivered':
        return 'Доставлен';
      case 'cancelled':
        return 'Отменен';
      default:
        return status;
    }
  }

  // Получить цвет статуса
  Color get statusColor {
    switch (status) {
      case 'pending':
        return Colors.orange;
      case 'confirmed':
        return Colors.blue;
      case 'preparing':
        return Colors.purple;
      case 'ready':
        return Colors.grey;
      case 'delivering':
        return Colors.indigo;
      case 'delivered':
        return Colors.green;
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}

class OrderItem {
  final int id;
  final int productId;
  final int quantity;
  final int price;
  final int total;
  final Map<String, dynamic> product;

  OrderItem({
    required this.id,
    required this.productId,
    required this.quantity,
    required this.price,
    required this.total,
    required this.product,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: json['id'],
      productId: json['product_id'],
      quantity: json['quantity'],
      price: json['price'],
      total: json['total'],
      product: json['product'],
    );
  }
}
