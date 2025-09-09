import 'package:flutter/material.dart';
import '../services/order_service.dart';

class ApiCheckoutScreen extends StatefulWidget {
  final Map<String, dynamic>? cartItems;

  const ApiCheckoutScreen({super.key, this.cartItems});

  @override
  _ApiCheckoutScreenState createState() => _ApiCheckoutScreenState();
}

class _ApiCheckoutScreenState extends State<ApiCheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  final _addressController = TextEditingController();
  final _phoneController = TextEditingController();
  final _nameController = TextEditingController();
  final _commentController = TextEditingController();

  String _paymentMethod = 'cash';
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  // Загрузить данные пользователя
  Future<void> _loadUserData() async {
    // Здесь можно загрузить сохраненные данные пользователя
    // Например, из SharedPreferences или Firebase
  }

  // Создать заказ через API
  Future<void> _createOrder() async {
    if (!_formKey.currentState!.validate()) {
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

      if (!mounted) return;

      if (result['success']) {
        // Заказ создан успешно
        final orderNumber = result['order']['order_number'] ?? 'N/A';
        _showSuccess('Заказ №$orderNumber создан успешно!');

        // Переходим на страницу успеха или истории заказов
        Navigator.of(context).pushNamedAndRemoveUntil(
          '/order-success',
          (route) => false,
          arguments: result['order'],
        );
      } else {
        // Ошибка создания заказа
        _showError(result['message'] ?? 'Неизвестная ошибка');

        // Показываем ошибки валидации если есть
        if (result['errors'] != null) {
          String errors = '';
          final Map<String, dynamic> errorMap = result['errors'];
          errorMap.forEach((field, messages) {
            if (messages is List) {
              errors += '${messages.join(', ')}\n';
            }
          });
          if (errors.isNotEmpty) {
            _showError(errors.trim());
          }
        }
      }
    } catch (e) {
      if (mounted) {
        _showError('Произошла ошибка: $e');
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  void _showError(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
        duration: const Duration(seconds: 5),
      ),
    );
  }

  void _showSuccess(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.green,
        duration: const Duration(seconds: 3),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Оформление заказа'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
      ),
      backgroundColor: const Color(0xFFF8F8F8),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16.0),
          children: [
            // Заголовок раздела
            const Row(
              children: [
                Icon(Icons.location_on_outlined, color: Color(0xFF22A447)),
                SizedBox(width: 8),
                Text(
                  'Адрес доставки',
                  style: TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 16,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Адрес доставки
            TextFormField(
              controller: _addressController,
              decoration: InputDecoration(
                labelText: 'Адрес доставки *',
                hintText: 'г. Ташкент, ул. Амира Темура 15, кв. 25',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                filled: true,
                fillColor: Colors.white,
              ),
              maxLines: 2,
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Введите адрес доставки';
                }
                return null;
              },
            ),

            const SizedBox(height: 16),

            // Телефон
            TextFormField(
              controller: _phoneController,
              decoration: InputDecoration(
                labelText: 'Телефон *',
                hintText: '+998901234567',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                filled: true,
                fillColor: Colors.white,
              ),
              keyboardType: TextInputType.phone,
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Введите номер телефона';
                }
                return null;
              },
            ),

            const SizedBox(height: 16),

            // Имя получателя
            TextFormField(
              controller: _nameController,
              decoration: InputDecoration(
                labelText: 'Имя получателя',
                hintText: 'Иван Иванов',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                filled: true,
                fillColor: Colors.white,
              ),
            ),

            const SizedBox(height: 24),

            // Заголовок оплаты
            const Row(
              children: [
                Icon(Icons.payment_outlined, color: Color(0xFF22A447)),
                SizedBox(width: 8),
                Text(
                  'Способ оплаты',
                  style: TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 16,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Способ оплаты
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: DropdownButtonFormField<String>(
                initialValue: _paymentMethod,
                decoration: const InputDecoration(
                  border: InputBorder.none,
                  labelText: 'Выберите способ оплаты',
                ),
                items: const [
                  DropdownMenuItem(
                    value: 'cash',
                    child: Row(
                      children: [
                        Icon(Icons.money, color: Colors.green),
                        SizedBox(width: 8),
                        Text('Наличные'),
                      ],
                    ),
                  ),
                  DropdownMenuItem(
                    value: 'card',
                    child: Row(
                      children: [
                        Icon(Icons.credit_card, color: Colors.blue),
                        SizedBox(width: 8),
                        Text('Картой при получении'),
                      ],
                    ),
                  ),
                  DropdownMenuItem(
                    value: 'online',
                    child: Row(
                      children: [
                        Icon(Icons.online_prediction, color: Colors.purple),
                        SizedBox(width: 8),
                        Text('Онлайн оплата'),
                      ],
                    ),
                  ),
                ],
                onChanged: (value) {
                  setState(() {
                    _paymentMethod = value!;
                  });
                },
              ),
            ),

            const SizedBox(height: 24),

            // Заголовок комментария
            const Row(
              children: [
                Icon(Icons.comment_outlined, color: Color(0xFF22A447)),
                SizedBox(width: 8),
                Text(
                  'Комментарий',
                  style: TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 16,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Комментарий
            TextFormField(
              controller: _commentController,
              decoration: InputDecoration(
                labelText: 'Комментарий к заказу',
                hintText: 'Позвонить за 10 минут до доставки',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                filled: true,
                fillColor: Colors.white,
              ),
              maxLines: 3,
            ),

            const SizedBox(height: 32),

            // Кнопка оформления
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _createOrder,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF22A447),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                  elevation: 0,
                ),
                child: _isLoading
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : const Text(
                        'Оформить заказ',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
              ),
            ),

            const SizedBox(height: 16),

            // Информация о доставке
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.blue.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.info_outline, color: Colors.blue),
                      const SizedBox(width: 8),
                      Text(
                        'Информация о доставке',
                        style: TextStyle(
                          fontWeight: FontWeight.w600,
                          color: Colors.blue.shade800,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '• Доставка осуществляется в течение 30-60 минут\n'
                    '• Минимальная сумма заказа: 50 000 сум\n'
                    '• Доставка по Ташкенту: 15 000 сум',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.blue.shade700,
                    ),
                  ),
                ],
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
