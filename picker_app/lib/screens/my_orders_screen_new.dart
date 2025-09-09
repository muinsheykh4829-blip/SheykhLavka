import 'package:flutter/material.dart';
import 'dart:async';
import '../services/api_service.dart';
import '../models/order.dart';

class MyOrdersScreen extends StatefulWidget {
  const MyOrdersScreen({super.key});

  @override
  State<MyOrdersScreen> createState() => _MyOrdersScreenState();
}

class _MyOrdersScreenState extends State<MyOrdersScreen> {
  List<Order> _orders = [];
  bool _isLoading = false;
  Timer? _timer;

  // Для отслеживания собранных товаров
  final Map<int, Map<int, bool>> _collectedItems = {};

  @override
  void initState() {
    super.initState();
    _loadOrders();
    _startAutoRefresh();
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  void _startAutoRefresh() {
    _timer = Timer.periodic(const Duration(seconds: 5), (timer) {
      _loadOrdersSilently();
    });
  }

  Future<void> _loadOrdersSilently() async {
    try {
      final result = await ApiService.getOrders(status: 'preparing');
      if (result['success'] == true && mounted) {
        setState(() {
          _orders = result['orders'] as List<Order>;
          _initializeCollectedItems();
        });
      }
    } catch (e) {
      // Тихое обновление, не показываем ошибки
    }
  }

  Future<void> _loadOrders() async {
    setState(() => _isLoading = true);

    try {
      final result = await ApiService.getOrders(status: 'preparing');
      if (result['success'] == true) {
        setState(() {
          _orders = result['orders'] as List<Order>;
          _initializeCollectedItems();
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Ошибка загрузки заказов: $e')),
        );
      }
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _initializeCollectedItems() {
    for (var order in _orders) {
      if (!_collectedItems.containsKey(order.id)) {
        _collectedItems[order.id] = {};
        for (var item in order.items) {
          _collectedItems[order.id]![item.id] = false;
        }
      }
    }
  }

  void _toggleItemCollected(int orderId, int itemId) {
    setState(() {
      _collectedItems[orderId]![itemId] =
          !(_collectedItems[orderId]![itemId] ?? false);
    });
  }

  bool _areAllItemsCollected(int orderId) {
    final orderItems = _collectedItems[orderId] ?? {};
    return orderItems.isNotEmpty &&
        orderItems.values.every((collected) => collected);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        title: const Text(
          'Мои заказы',
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 18,
          ),
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          color: Color(0xFF4CAF50),
        ),
      );
    }

    if (_orders.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.work_outline,
              color: Colors.grey,
              size: 64,
            ),
            SizedBox(height: 16),
            Text(
              'Нет заказов в работе',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w500,
                color: Colors.grey,
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadOrders,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _orders.length,
        itemBuilder: (context, index) {
          return _buildOrderCard(_orders[index]);
        },
      ),
    );
  }

  Widget _buildOrderCard(Order order) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            offset: const Offset(0, 2),
            blurRadius: 8,
            spreadRadius: 0,
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Заголовок с номером заказа и иконкой корзины
            Row(
              children: [
                Expanded(
                  child: Text(
                    'Заказ № ${order.orderNumber}',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: Colors.black87,
                    ),
                  ),
                ),
                const Icon(
                  Icons.shopping_cart_outlined,
                  color: Colors.grey,
                  size: 24,
                ),
              ],
            ),
            const SizedBox(height: 8),

            // Информация о магазине
            Text(
              'Магазин: Лента',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 4),

            // Адрес доставки
            Text(
              'Адрес: ${order.deliveryAddress}',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 16),

            // Список товаров с чекбоксами
            if (order.items.isNotEmpty) ...[
              ...order.items.map((item) {
                final isCollected =
                    _collectedItems[order.id]?[item.id] ?? false;
                return Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: Row(
                    children: [
                      // Чекбокс
                      GestureDetector(
                        onTap: () => _toggleItemCollected(order.id, item.id),
                        child: Container(
                          width: 24,
                          height: 24,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: isCollected
                                  ? const Color(0xFF4CAF50)
                                  : Colors.grey,
                              width: 2,
                            ),
                            color: isCollected
                                ? const Color(0xFF4CAF50)
                                : Colors.transparent,
                          ),
                          child: isCollected
                              ? const Icon(
                                  Icons.check,
                                  color: Colors.white,
                                  size: 16,
                                )
                              : null,
                        ),
                      ),
                      const SizedBox(width: 12),

                      // Название товара и количество
                      Expanded(
                        child: Text(
                          '${item.productName} - ${item.quantity} шт.',
                          style: TextStyle(
                            fontSize: 14,
                            color:
                                isCollected ? Colors.grey[500] : Colors.black87,
                            decoration:
                                isCollected ? TextDecoration.lineThrough : null,
                          ),
                        ),
                      ),

                      // Правый чекбокс (как на макете)
                      Container(
                        width: 24,
                        height: 24,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: Colors.grey,
                            width: 2,
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              }),
              const SizedBox(height: 16),
            ],

            // Кнопка "Завершить"
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _areAllItemsCollected(order.id)
                    ? () => _completeOrder(order)
                    : null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: _areAllItemsCollected(order.id)
                      ? const Color(0xFF4CAF50)
                      : Colors.grey[300],
                  foregroundColor: Colors.white,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
                child: Text(
                  'Завершить',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: _areAllItemsCollected(order.id)
                        ? Colors.white
                        : Colors.grey[600],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _completeOrder(Order order) async {
    try {
      final result = await ApiService.completeOrder(order.id);

      if (result['success'] == true) {
        // Убираем заказ из списка
        setState(() {
          _orders.removeWhere((o) => o.id == order.id);
          _collectedItems.remove(order.id);
        });

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Заказ завершен'),
            backgroundColor: Color(0xFF4CAF50),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Ошибка завершения заказа'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Ошибка: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}
