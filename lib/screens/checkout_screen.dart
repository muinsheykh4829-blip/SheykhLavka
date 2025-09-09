import 'package:flutter/material.dart';
import 'address_selector.dart';
import 'package:provider/provider.dart';
import '../models/cart_model.dart';
import 'payment_selector.dart';
import 'cart_summary.dart';
import 'order_success_screen.dart';
import '../services/api_service.dart';
import 'package:shared_preferences/shared_preferences.dart';

class CheckoutScreen extends StatelessWidget {
  const CheckoutScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text('Оформление заказа',
            style: TextStyle(fontWeight: FontWeight.bold)),
        centerTitle: true,
        backgroundColor: Colors.white,
        elevation: 0,
      ),
      backgroundColor: const Color(0xFFF8F8F8),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const SizedBox(height: 8),
          const Row(
            children: [
              Icon(Icons.person_outline, color: Color(0xFF22A447)),
              SizedBox(width: 8),
              Text('Персональная информация',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
            ],
          ),
          const SizedBox(height: 12),
          const AddressSelector(),
          const SizedBox(height: 16),
          const Text('Комментарий для курьера',
              style: TextStyle(fontSize: 13, color: Colors.black54)),
          const SizedBox(height: 6),
          TextField(
            maxLines: 3,
            decoration: InputDecoration(
              hintText: 'Например: Оставьте у двери и позвоните в звонок.',
              filled: true,
              fillColor: const Color(0xFFF2F2F2),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: BorderSide.none,
              ),
              contentPadding:
                  const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
            ),
          ),
          const SizedBox(height: 24),
          const Row(
            children: [
              Icon(Icons.credit_card, color: Color(0xFF22A447)),
              SizedBox(width: 8),
              Text('Способ оплаты',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
            ],
          ),
          const SizedBox(height: 12),
          Container(
            child: const PaymentSelector(),
          ),
          const SizedBox(height: 24),
          const Row(
            children: [
              Icon(Icons.receipt_long, color: Color(0xFF22A447)),
              SizedBox(width: 8),
              Text('Сводка заказа',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
            ],
          ),
          const SizedBox(height: 12),
          Builder(
            builder: (context) {
              final cart = context.watch<CartModel>();
              return CartSummary(
                  total: cart.total, delivery: cart.deliveryCost);
            },
          ),
          const SizedBox(height: 24),
// ...
        ],
      ),
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.fromLTRB(8, 0, 8, 12),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Предупреждение о минимальной сумме
            Consumer<CartModel>(
              builder: (context, cart, child) {
                if (!cart.isMinimumOrderReached) {
                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding:
                        const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: Colors.orange.shade100,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.orange),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.warning_amber_rounded,
                            color: Colors.orange.shade700, size: 20),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            cart.minimumOrderText,
                            style: TextStyle(
                              color: Colors.orange.shade700,
                              fontSize: 13,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                }
                return const SizedBox.shrink();
              },
            ),
            SizedBox(
              height: 48,
              child: Builder(
                builder: (context) {
                  final cart = context.watch<CartModel>();
                  final address = cart.address;
                  final enabled =
                      address.isNotEmpty && cart.isMinimumOrderReached;
                  return ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF22A447),
                      foregroundColor: Colors.white,
                      textStyle: const TextStyle(
                          fontWeight: FontWeight.bold, fontSize: 17),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    onPressed: enabled
                        ? () async {
                            try {
                              // Показываем индикатор загрузки
                              showDialog(
                                context: context,
                                barrierDismissible: false,
                                builder: (context) => const Center(
                                  child: CircularProgressIndicator(),
                                ),
                              );

                              // Подготавливаем данные товаров
                              final items = cart.items
                                  .map((cartItem) => {
                                        'product_id': cartItem.product.id,
                                        'quantity': cartItem.qty,
                                        if (cartItem.weight != null)
                                          'weight': cartItem.weight,
                                      })
                                  .toList();

                              // Получаем данные пользователя из SharedPreferences
                              final prefs =
                                  await SharedPreferences.getInstance();
                              final firstName =
                                  prefs.getString('profile_first_name') ?? '';
                              final lastName =
                                  prefs.getString('profile_last_name') ?? '';
                              final userPhone = prefs.getString('phone') ??
                                  prefs.getString('user_phone') ??
                                  '';

                              // Отладочная информация
                              print('📱 Данные пользователя для заказа:');
                              print('   Имя: "$firstName"');
                              print('   Фамилия: "$lastName"');
                              print(
                                  '   Телефон из profile: "${prefs.getString('phone')}"');
                              print(
                                  '   Телефон из user_phone: "${prefs.getString('user_phone')}"');
                              print('   Итоговый телефон: "$userPhone"');

                              // Формируем полное имя
                              String fullName =
                                  'Пользователь'; // Значение по умолчанию
                              if (firstName.isNotEmpty || lastName.isNotEmpty) {
                                fullName = '$firstName $lastName'.trim();
                                if (fullName.isEmpty) fullName = 'Пользователь';
                              }

                              // Используем номер телефона из профиля или значение по умолчанию
                              String phoneNumber =
                                  '+992000000000'; // Значение по умолчанию
                              if (userPhone.isNotEmpty) {
                                phoneNumber = userPhone;
                              }

                              print('   Итоговое имя: "$fullName"');
                              print('   Итоговый телефон: "$phoneNumber"');
                              print(
                                  '🚚 Тип доставки из корзины: ${cart.deliveryType}');
                              print(
                                  '🚚 Тип доставки для API: ${cart.deliveryType == DeliveryType.express ? 'express' : 'standard'}');

                              // Отправляем заказ в API
                              final result = await ApiService.createOrder(
                                deliveryAddress: cart.address,
                                deliveryPhone: phoneNumber,
                                deliveryName: fullName,
                                paymentMethod:
                                    cart.payment == PaymentMethod.card
                                        ? 'card'
                                        : 'cash',
                                comment: 'Заказ из мобильного приложения',
                                deliveryType:
                                    cart.deliveryType == DeliveryType.express
                                        ? 'express'
                                        : 'standard',
                                items: items, // Добавляем товары
                              );

                              // Закрываем индикатор загрузки
                              if (context.mounted) {
                                Navigator.of(context, rootNavigator: true)
                                    .pop();
                              }

                              if (result['success'] == true) {
                                // Заказ успешно создан
                                cart.clear();

                                if (context.mounted) {
                                  Navigator.of(context).push(
                                    PageRouteBuilder(
                                      opaque: false,
                                      barrierDismissible: false,
                                      barrierColor:
                                          Colors.black.withOpacity(0.35),
                                      pageBuilder: (_, __, ___) =>
                                          const OrderSuccessScreen(),
                                      transitionsBuilder: (
                                        _,
                                        animation,
                                        __,
                                        child,
                                      ) =>
                                          FadeTransition(
                                        opacity: CurvedAnimation(
                                          parent: animation,
                                          curve: Curves.easeOut,
                                        ),
                                        child: child,
                                      ),
                                    ),
                                  );
                                }
                              } else {
                                // Показываем ошибку
                                if (context.mounted) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(result['message'] ??
                                          'Ошибка оформления заказа'),
                                      backgroundColor: Colors.red,
                                    ),
                                  );
                                }
                              }
                            } catch (e) {
                              // Закрываем индикатор загрузки в случае ошибки
                              if (context.mounted) {
                                Navigator.of(context, rootNavigator: true)
                                    .pop();
                              }

                              // Показываем ошибку
                              if (context.mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text('Ошибка подключения: $e'),
                                    backgroundColor: Colors.red,
                                  ),
                                );
                              }
                            }
                          }
                        : null,
                    child: const Text('Оформить заказ'),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}
