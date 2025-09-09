import 'package:flutter/material.dart';
import 'dart:async';
import 'package:provider/provider.dart';
import 'package:flutter_ringtone_player/flutter_ringtone_player.dart';
import '../services/api_service.dart';
import '../models/order.dart';
import '../providers/auth_provider.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen>
    with TickerProviderStateMixin {
  List<Order> _orders = [];
  bool _isLoading = false;
  bool _isAutoRefreshing = false;
  Timer? _timer;
  late AnimationController _animationController;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 500),
      vsync: this,
    );
    _loadOrders();
    _startAutoRefresh();
  }

  @override
  void dispose() {
    _timer?.cancel();
    _animationController.dispose();
    super.dispose();
  }

  void _startAutoRefresh() {
    _timer = Timer.periodic(const Duration(seconds: 3), (timer) {
      _loadOrdersSilently();
    });
  }

  int _lastOrdersCount = 0; // хранение предыдущего количества заказов

  Future<void> _loadOrdersSilently() async {
    setState(() => _isAutoRefreshing = true);
    _animationController.repeat();

    try {
      final result = await ApiService.getOrders(status: 'accepted');
      if (result['success'] == true && mounted) {
        final newList = result['orders'] as List<Order>;
        // если появилось больше заказов чем раньше -> звук
        if (_lastOrdersCount > 0 && newList.length > _lastOrdersCount) {
          FlutterRingtonePlayer.playNotification();
        }
        _lastOrdersCount = newList.length;
        setState(() {
          _orders = newList;
        });
      }
    } catch (e) {
      // Тихое обновление, не показываем ошибки
    } finally {
      setState(() => _isAutoRefreshing = false);
      _animationController.stop();
      _animationController.reset();
    }
  }

  Future<void> _loadOrders() async {
    setState(() => _isLoading = true);

    try {
      final result = await ApiService.getOrders(status: 'accepted');
      if (result['success'] == true) {
        final newList = result['orders'] as List<Order>;
        if (_lastOrdersCount > 0 && newList.length > _lastOrdersCount) {
          FlutterRingtonePlayer.playNotification();
        }
        _lastOrdersCount = newList.length;
        setState(() {
          _orders = newList;
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

  Future<void> _logout() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    await authProvider.logout();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        title: const Text(
          'Новые заказы',
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 18,
          ),
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        actions: [
          if (_isAutoRefreshing)
            Padding(
              padding: const EdgeInsets.only(right: 16),
              child: AnimatedBuilder(
                animation: _animationController,
                builder: (context, child) {
                  return Transform.rotate(
                    angle: _animationController.value * 2 * 3.14159,
                    child: const Icon(
                      Icons.refresh,
                      color: Colors.grey,
                    ),
                  );
                },
              ),
            ),
          IconButton(
            onPressed: _logout,
            icon: const Icon(Icons.logout, color: Colors.grey),
            tooltip: 'Выйти',
          ),
        ],
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
              Icons.inbox_outlined,
              color: Colors.grey,
              size: 64,
            ),
            SizedBox(height: 16),
            Text(
              'Нет новых заказов',
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
      margin: const EdgeInsets.only(bottom: 12),
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

            // Адрес доставки
            Text(
              'Адрес: ${order.deliveryAddress}',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 4),

            // Тип доставки
            Row(
              children: [
                Icon(
                  order.deliveryType == 'express'
                      ? Icons.flash_on
                      : Icons.local_shipping,
                  size: 16,
                  color: order.deliveryType == 'express'
                      ? Colors.orange
                      : Colors.green,
                ),
                const SizedBox(width: 4),
                Text(
                  order.deliveryTypeName,
                  style: TextStyle(
                    fontSize: 14,
                    color: order.deliveryType == 'express'
                        ? Colors.orange
                        : Colors.green,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Позиции товаров
            if (order.items.isNotEmpty) ...[
              Text(
                'Позиции:',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: Colors.grey[700],
                ),
              ),
              const SizedBox(height: 8),
              ...order.items.map((item) => Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '• ${item.productName} - ${item.displayQuantity}',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            color: Colors.grey[700],
                          ),
                        ),
                        if (item.product?.descriptionRu != null &&
                            item.product!.descriptionRu!.isNotEmpty) ...[
                          const SizedBox(height: 2),
                          Padding(
                            padding: const EdgeInsets.only(left: 12),
                            child: Text(
                              item.product!.descriptionRu!,
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey[500],
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  )),
              const SizedBox(height: 16),
            ] else ...[
              Text(
                'Позиции: ${order.itemsCount} товар(ов)',
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey[600],
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Кнопка "Принять заказ"
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => _takeOrder(order),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4CAF50),
                  foregroundColor: Colors.white,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
                child: const Text(
                  'Принять заказ',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _takeOrder(Order order) async {
    try {
      final result = await ApiService.takeOrder(order.id);

      if (result['success'] == true) {
        // Убираем заказ из списка
        setState(() {
          _orders.removeWhere((o) => o.id == order.id);
        });

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Заказ принят в работу'),
            backgroundColor: Color(0xFF4CAF50),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Ошибка принятия заказа'),
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
