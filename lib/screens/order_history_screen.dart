import 'package:flutter/material.dart';
import '../widgets/modern_loader.dart';
// Экран подробностей заказа временно отключен; будет карта отслеживания
import 'tracking_stub_screen.dart';

import '../models/order.dart';
import '../repository/order_repository.dart';
import '../services/api_service.dart';
import '../config/api_config.dart';

class OrderHistoryScreen extends StatefulWidget {
  final bool asSheet;
  const OrderHistoryScreen({super.key, this.asSheet = false});

  @override
  State<OrderHistoryScreen> createState() => _OrderHistoryScreenState();
}

class _OrderHistoryScreenState extends State<OrderHistoryScreen> {
  List<Order> orders = [];
  bool isLoading = true;
  String? errorMessage;

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  Future<void> _loadOrders() async {
    try {
      setState(() {
        isLoading = true;
        errorMessage = null;
      });

      // Отладочная информация
      final isAuth = await ApiService.isAuthenticated();
      final token = await ApiService.getToken();
      print('🔐 Пользователь авторизован: $isAuth');
      print('🎫 Токен: ${token ?? 'НЕТ ТОКЕНА'}');

      final loadedOrders = await OrderRepository.loadOrders();
      print('📦 Загружено заказов: ${loadedOrders.length}');

      setState(() {
        orders = loadedOrders;
        isLoading = false;
      });
    } catch (e) {
      print('❌ Ошибка загрузки заказов: $e');
      setState(() {
        errorMessage = 'Ошибка загрузки заказов: $e';
        isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    // Показываем индикатор загрузки
    if (isLoading) {
      return Scaffold(
        backgroundColor: Colors.white,
        appBar: AppBar(
          title: const Text('Мои заказы'),
          centerTitle: true,
        ),
        body: const Center(child: ModernLoader(label: 'Загружаем заказы...')),
      );
    }

    // Показываем ошибку
    if (errorMessage != null) {
      return Scaffold(
        backgroundColor: Colors.white,
        appBar: AppBar(
          title: const Text('Мои заказы'),
          centerTitle: true,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.error_outline,
                size: 64,
                color: Colors.grey[400],
              ),
              const SizedBox(height: 16),
              Text(
                errorMessage!,
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[600]),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _loadOrders,
                child: const Text('Повторить'),
              ),
            ],
          ),
        ),
      );
    }

    // Если заказов нет – просто показываем пустой экран с AppBar
    if (orders.isEmpty) {
      return Scaffold(
        backgroundColor: Colors.white,
        appBar: AppBar(
          title: const Text('Мои заказы'),
          centerTitle: true,
        ),
        body: const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.assignment_outlined,
                size: 120,
                color: Color(0xFF9E9E9E),
              ),
              SizedBox(height: 24),
              Text(
                'История заказов',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w600,
                  color: Colors.black87,
                ),
              ),
              SizedBox(height: 12),
              Text(
                'У вас нет заказов',
                style: TextStyle(
                  fontSize: 16,
                  color: Color(0xFF9E9E9E),
                ),
              ),
            ],
          ),
        ),
      );
    }

    final listView = ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: orders.length,
      itemBuilder: (context, i) {
        final order = orders[i];
        return InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => _openOrderQuickDetails(order),
          child: Card(
            color: Colors.white,
            margin: const EdgeInsets.only(bottom: 16),
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          const CircleAvatar(
                            backgroundColor: Color(0xFF222222),
                            radius: 18,
                            child: Icon(Icons.store,
                                color: Colors.white, size: 20),
                          ),
                          const SizedBox(width: 10),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Заказ принят',
                                  style:
                                      TextStyle(fontWeight: FontWeight.bold)),
                              Text(
                                _formatDate(order.createdAt),
                                style: const TextStyle(
                                    fontSize: 13, color: Colors.grey),
                              ),
                            ],
                          ),
                        ],
                      ),
                      _buildStatus(order.status),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text('Сумма заказа: ${(order.total).toStringAsFixed(2)} с',
                      style: const TextStyle(fontSize: 16)),
                  Text('Количество товаров: ${order.items.length} шт',
                      style:
                          const TextStyle(fontSize: 15, color: Colors.black54)),
                  const SizedBox(height: 12),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Expanded(
                        child: Text(
                          'Оплата: ${_getPaymentMethodName(order.paymentMethod)}',
                          style: const TextStyle(
                              fontSize: 15, color: Colors.black54),
                        ),
                      ),
                      // Кнопка отслеживания: показываем для любых активных статусов,
                      // кроме завершенных или отмененных
                      if (!['delivered', 'cancelled'].contains(order.status))
                        OutlinedButton(
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 8),
                            side: const BorderSide(
                                color: Color(0xFF22A447), width: 1),
                            foregroundColor: const Color(0xFF22A447),
                            textStyle: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          onPressed: () {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => TrackingStubScreen(
                                  orderNumber: order.orderNumber,
                                ),
                              ),
                            );
                          },
                          child: const Text('Отследить'),
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );

    if (widget.asSheet) {
      return SafeArea(
        top: false,
        child: Column(
          children: [
            const SizedBox(height: 6),
            Container(
              width: 44,
              height: 4,
              decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2)),
            ),
            const SizedBox(height: 12),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Мои заказы',
                      style:
                          TextStyle(fontSize: 20, fontWeight: FontWeight.w600)),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.of(context).pop(),
                  )
                ],
              ),
            ),
            const SizedBox(height: 4),
            Expanded(child: listView),
          ],
        ),
      );
    }

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: const Text('Мои заказы'),
        centerTitle: true,
      ),
      body: listView,
    );
  } // конец build

  Widget _buildStatus(String status) {
    // Используем логику из модели Order
    String statusText;
    Color statusColor;
    Color backgroundColor;
    Color borderColor;

    switch (status) {
      case 'processing':
        statusText = 'В обработке';
        statusColor = Colors.orange;
        backgroundColor = Colors.orange.withOpacity(0.1);
        borderColor = Colors.orange;
        break;
      case 'accepted':
        statusText = 'Принят';
        statusColor = Colors.blue;
        backgroundColor = Colors.blue.withOpacity(0.1);
        borderColor = Colors.blue;
        break;
      case 'preparing':
        statusText = 'Собирается';
        statusColor = Colors.purple;
        backgroundColor = Colors.purple.withOpacity(0.1);
        borderColor = Colors.purple;
        break;
      case 'ready':
        statusText = 'Собран';
        statusColor = Colors.teal;
        backgroundColor = Colors.teal.withOpacity(0.1);
        borderColor = Colors.teal;
        break;
      case 'delivering':
        statusText = 'Курьер в пути';
        statusColor = Colors.indigo;
        backgroundColor = Colors.indigo.withOpacity(0.1);
        borderColor = Colors.indigo;
        break;
      case 'delivered':
        statusText = 'Завершен';
        statusColor = Colors.green;
        backgroundColor = const Color(0xFFDFF6E6);
        borderColor = const Color(0xFF22A447);
        break;
      case 'cancelled':
        statusText = 'Отменен';
        statusColor = Colors.red;
        backgroundColor = Colors.red.withOpacity(0.1);
        borderColor = Colors.red;
        break;
      default:
        statusText = status;
        statusColor = Colors.grey;
        backgroundColor = Colors.grey.shade200;
        borderColor = Colors.grey;
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: borderColor),
      ),
      child: Text(
        statusText,
        style: TextStyle(
          color: statusColor,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day.toString().padLeft(2, '0')}.${date.month.toString().padLeft(2, '0')}.${date.year} ${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
  }

  String _getPaymentMethodName(String paymentMethod) {
    switch (paymentMethod.toLowerCase()) {
      case 'cash':
        return 'наличные';
      case 'card':
        return 'карта';
      case 'online':
        return 'онлайн';
      default:
        return paymentMethod;
    }
  }

  void _openOrderQuickDetails(Order order) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => _OrderQuickDetailsSheet(order: order),
    );
  }
}

class _OrderQuickDetailsSheet extends StatelessWidget {
  final Order order;
  const _OrderQuickDetailsSheet({required this.order});

  // Преобразование относительных путей ('uploads/...', 'storage/...') в абсолютный URL
  static String _resolveImageUrl(String path) {
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    var cleaned = path.startsWith('/') ? path.substring(1) : path;
    // Часто backend возвращает просто 'uploads/...' или 'storage/...'
    if (cleaned.startsWith('uploads/') || cleaned.startsWith('storage/')) {
      return ApiConfig.fileUrl(cleaned);
    }
    // fallback: если это просто имя файла, пытаемся через uploads
    return ApiConfig.fileUrl('uploads/$cleaned');
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      top: false,
      child: Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 8),
            Center(
              child: Container(
                width: 44,
                height: 5,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
            ),
            const SizedBox(height: 12),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Заказ #${order.orderNumber}',
                      style: const TextStyle(
                          fontSize: 20, fontWeight: FontWeight.w600)),
                  _statusChip(order),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
              child: Text(
                _formatFullDate(order.createdAt),
                style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
              ),
            ),
            const SizedBox(height: 4),
            // Список товаров
            Flexible(
              child: ListView.separated(
                shrinkWrap: true,
                padding: const EdgeInsets.symmetric(horizontal: 12),
                itemCount: order.items.length,
                separatorBuilder: (_, __) => Divider(
                  height: 1,
                  color: Colors.grey.shade200,
                  indent: 72,
                ),
                itemBuilder: (ctx, i) {
                  final item = order.items[i];
                  final product = item.product;
                  // Изображение товара: берём product['image'] или первый из product['images']
                  String? image = (product['image'] as String?)?.trim();
                  if ((image == null || image.isEmpty) &&
                      product['images'] is List &&
                      (product['images'] as List).isNotEmpty) {
                    final first = (product['images'] as List).first;
                    if (first is String) image = first.trim();
                  }
                  // Нормализуем относительный путь в абсолютный URL
                  if (image != null && image.isNotEmpty) {
                    image = _resolveImageUrl(image);
                  }
                  final title =
                      (product['name_ru'] ?? product['name'] ?? '').toString();
                  final description = (product['description_ru'] ??
                          product['description'] ??
                          '')
                      .toString();
                  // Показываем либо точный вес (кг с 3 знаками), либо количество (шт)
                  final String qtyLabel =
                      (item.weight != null && item.weight! > 0)
                          ? '${item.weight!.toStringAsFixed(3)} кг'
                          : '${item.quantity} шт';
                  return Padding(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 4, vertical: 10),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(14),
                          child: Container(
                            width: 56,
                            height: 56,
                            color: Colors.grey.shade100,
                            child: image != null && image.isNotEmpty
                                ? Image.network(
                                    image,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) => const Icon(
                                      Icons.broken_image,
                                      color: Colors.grey,
                                    ),
                                  )
                                : const Icon(Icons.image, color: Colors.grey),
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                title,
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              if (description.isNotEmpty) ...[
                                const SizedBox(height: 3),
                                Text(
                                  description,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    fontSize: 12,
                                    color: Colors.black54,
                                    height: 1.2,
                                  ),
                                ),
                              ],
                              const SizedBox(height: 4),
                              Text(
                                qtyLabel,
                                style: const TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          '${item.total.toStringAsFixed(2)} c',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
            const Divider(height: 1),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _row('Доставка', '${order.deliveryFee.toStringAsFixed(2)} c'),
                  const SizedBox(height: 8),
                  _row('Итого', '${order.total.toStringAsFixed(2)} c',
                      bold: true),
                ],
              ),
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  static Widget _row(String label, String value, {bool bold = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label,
            style: TextStyle(fontSize: 14, color: Colors.grey.shade600)),
        Text(
          value,
          style: TextStyle(
            fontSize: 14,
            fontWeight: bold ? FontWeight.w600 : FontWeight.w400,
          ),
        ),
      ],
    );
  }

  static Widget _statusChip(Order order) {
    final status = order.statusName; // локализованный
    final color = order.statusColor; // цвет из модели
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        status,
        style:
            TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.w600),
      ),
    );
  }

  static String _formatFullDate(DateTime date) {
    return '${date.day} ${_monthName(date)} ${date.year} в ${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
  }

  static String _monthName(DateTime date) {
    const months = [
      'Янв',
      'Фев',
      'Мар',
      'Апр',
      'Май',
      'Июн',
      'Июл',
      'Авг',
      'Сен',
      'Окт',
      'Ноя',
      'Дек'
    ];
    return months[date.month - 1];
  }
}
