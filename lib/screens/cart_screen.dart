import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/cart_model.dart';
import '../widgets/weight_calculator_dialog.dart';
import 'checkout_screen.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  // deliveryType теперь хранится в CartModel

  void _changeWeight(CartItem item, CartModel cart) {
    showDialog(
      context: context,
      builder: (context) => WeightCalculatorDialog(
        productName: item.product.getLocalizedName(),
        productPrice: item.product.price,
        unit: item.product.unit,
        onConfirm: (newWeight) {
          cart.updateWeight(item.product, newWeight);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartModel>();
    int delivery = cart.deliveryCost;
    final double screenHeight = MediaQuery.of(context).size.height;
    return SafeArea(
      child: Container(
        margin: EdgeInsets.zero,
        height: screenHeight * 0.8,
        decoration: const BoxDecoration(
          color: Color(0xFFF8F8F8),
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.max,
          children: [
            // ...existing code...
            const SizedBox(height: 12),
            const Text('Детали заказа',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Expanded(
              child: Column(
                children: [
                  Expanded(
                    child: ListView.separated(
                      padding: const EdgeInsets.all(16),
                      itemCount: cart.items.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 16),
                      itemBuilder: (context, i) {
                        final item = cart.items[i];
                        return CartItemWidget(
                          item: item,
                          onWeightChange: _changeWeight,
                        );
                      },
                    ),
                  ),
                  // Delivery time selector
                  Padding(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.access_time, size: 20),
                            SizedBox(width: 8),
                            Text('Время доставки',
                                style: TextStyle(fontWeight: FontWeight.w600)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            _DeliveryOption(
                              title: 'Экспресс',
                              subtitle: '25-30 минут',
                              price: '10 с',
                              selected:
                                  cart.deliveryType == DeliveryType.express,
                              onTap: () {
                                cart.setDeliveryType(DeliveryType.express);
                                setState(() {});
                              },
                            ),
                            const SizedBox(width: 12),
                            _DeliveryOption(
                              title: 'Стандарт',
                              subtitle: '2-3 часа',
                              price: 'бесплатно',
                              selected:
                                  cart.deliveryType == DeliveryType.standart,
                              onTap: () {
                                cart.setDeliveryType(DeliveryType.standart);
                                setState(() {});
                              },
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            CartSummary(total: cart.total, delivery: delivery),
          ],
        ),
      ),
    );
  }
}

class _DeliveryOption extends StatelessWidget {
  final String title;
  final String subtitle;
  final String price;
  final bool selected;
  final VoidCallback onTap;
  const _DeliveryOption({
    required this.title,
    required this.subtitle,
    required this.price,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          constraints: const BoxConstraints(minHeight: 70),
          decoration: BoxDecoration(
            color: selected ? Colors.white : Colors.grey[100],
            border: Border.all(
              color: selected ? const Color(0xFF22A447) : Colors.grey[300]!,
              width: selected ? 2 : 1,
            ),
            borderRadius: BorderRadius.circular(14),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                children: [
                  Text(title,
                      style: const TextStyle(
                          fontWeight: FontWeight.w600, fontSize: 16)),
                  const Spacer(),
                  if (selected)
                    const Icon(Icons.check_circle,
                        color: Color(0xFF22A447), size: 20),
                  if (!selected)
                    const Icon(Icons.radio_button_unchecked,
                        color: Colors.grey, size: 20),
                ],
              ),
              const SizedBox(height: 2),
              Text(subtitle,
                  style: const TextStyle(fontSize: 13, color: Colors.black54)),
              Text(price,
                  style: const TextStyle(fontSize: 13, color: Colors.black54)),
            ],
          ),
        ),
      ),
    );
  }
}

class CartItemWidget extends StatelessWidget {
  final CartItem item;
  final Function(CartItem, CartModel)? onWeightChange;
  const CartItemWidget({super.key, required this.item, this.onWeightChange});

  void _showDeleteConfirmation(BuildContext context, CartModel cart) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          backgroundColor: Colors.white,
          title: const Text('Удалить товар'),
          content: Text('Удалить "${item.product.name}" из корзины?'),
          actions: [
            TextButton(
              child: const Text('Отмена'),
              onPressed: () => Navigator.of(context).pop(),
            ),
            TextButton(
              child: const Text('Удалить', style: TextStyle(color: Colors.red)),
              onPressed: () {
                cart.removeItem(item.product);
                Navigator.of(context).pop();
              },
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.read<CartModel>();
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Изображение товара
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: Colors.grey[200],
                borderRadius: BorderRadius.circular(12),
              ),
              child: item.product.primaryImage.isNotEmpty
                  ? Image.network(
                      item.product.primaryImage,
                      width: 60,
                      height: 60,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) {
                        return Container(
                          width: 60,
                          height: 60,
                          color: Colors.grey[200],
                          child: const Icon(
                            Icons.image_not_supported,
                            color: Colors.grey,
                            size: 24,
                          ),
                        );
                      },
                    )
                  : Container(
                      width: 60,
                      height: 60,
                      color: Colors.grey[200],
                      child: const Icon(
                        Icons.shopping_bag_outlined,
                        color: Colors.grey,
                        size: 24,
                      ),
                    ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(item.product.name,
                                style: const TextStyle(fontSize: 14)),
                          ),
                          if (!item.product.inStock)
                            Container(
                              margin: const EdgeInsets.only(left: 6),
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFFE5E5),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Text(
                                'Закончился',
                                style: TextStyle(
                                  color: Color(0xFFD7263D),
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: () => _showDeleteConfirmation(context, cart),
                      icon: const Icon(Icons.delete_outline),
                      iconSize: 20,
                      color: Colors.red,
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(
                        minWidth: 24,
                        minHeight: 24,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 2),
                Text(item.product.descriptionRu,
                    style:
                        const TextStyle(fontSize: 12, color: Colors.black54)),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(item.formattedTotalWithKopecks,
                  style: const TextStyle(
                      fontSize: 17,
                      color: Color(0xFFFF9900),
                      fontWeight: FontWeight.bold)),
              const SizedBox(height: 6),
              // Проверяем, является ли товар весовым
              item.isWeightProduct
                  ? GestureDetector(
                      onTap: () => onWeightChange?.call(item, cart),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF2F2F2),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: const Color(0xFF22A447).withOpacity(0.3),
                          ),
                        ),
                        child: Text(
                          item.displayQuantity,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF22A447),
                          ),
                        ),
                      ),
                    )
                  : Row(
                      children: [
                        QtyButton(
                            icon: Icons.remove,
                            onTap: () => cart.dec(item.product)),
                        Container(
                          margin: const EdgeInsets.symmetric(horizontal: 4),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF2F2F2),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text('${item.qty}',
                              style:
                                  const TextStyle(fontWeight: FontWeight.bold)),
                        ),
                        QtyButton(
                            icon: Icons.add,
                            filled: true,
                            onTap: item.product.inStock
                                ? () => cart.add(item.product)
                                : () {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(
                                        content: Text(
                                            'Товар закончился и временно недоступен'),
                                      ),
                                    );
                                  }),
                      ],
                    ),
            ],
          ),
        ],
      ),
    );
  }
}

class QtyButton extends StatelessWidget {
  final IconData icon;
  final bool filled;
  final VoidCallback onTap;
  const QtyButton(
      {super.key,
      required this.icon,
      this.filled = false,
      required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: filled ? const Color(0xFF22A447) : Colors.white,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: const Color(0xFF22A447)),
      ),
      child: IconButton(
        icon:
            Icon(icon, color: filled ? Colors.white : const Color(0xFF22A447)),
        onPressed: onTap,
        iconSize: 20,
        padding: const EdgeInsets.all(0),
        constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
      ),
    );
  }
}

class CartSummary extends StatelessWidget {
  final double total;
  final int delivery;
  const CartSummary({super.key, required this.total, required this.delivery});

  @override
  Widget build(BuildContext context) {
    const discount = 0;
    // total уже в сомони (двойная конвертация не требуется)
    final totalInSom = total;
    final sum = totalInSom + delivery - discount;
    final bool canOrder =
        totalInSom >= 100; // минимум 100 сом (без учета доставки)
    return Container(
      margin: const EdgeInsets.only(top: 8),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(18),
        gradient: const LinearGradient(
          colors: [Color(0xFF22A447), Color(0xFF4ADE80)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          SummaryRow(
              label: 'Промежуточный итог',
              value: '${totalInSom.toStringAsFixed(2)} с'),
          SummaryRow(
              label: 'Доставка',
              value: delivery == 0
                  ? 'бесплатный'
                  : '${delivery.toStringAsFixed(2)} с'),
          const Divider(color: Colors.white70, height: 16),
          SummaryRow(
              label: 'Итого', value: '${sum.toStringAsFixed(2)} с', bold: true),
          const SizedBox(height: 10),
          SizedBox(
            width: double.infinity,
            height: 44,
            child: Builder(
              builder: (context) => ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: canOrder
                      ? Colors.white
                      : const Color.fromARGB(90, 255, 255, 255),
                  foregroundColor: const Color(0xFF22A447),
                  padding: EdgeInsets.zero,
                  textStyle: const TextStyle(
                      fontSize: 16, fontWeight: FontWeight.bold),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
                onPressed: canOrder
                    ? () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => const CheckoutScreen(),
                          ),
                        );
                      }
                    : null,
                child: const Text('Оформить заказ'),
              ),
            ),
          ),
          if (!canOrder)
            Padding(
              padding: const EdgeInsets.only(top: 6),
              child: Text(
                'Минимальная сумма заказа 100 сом. Добавьте еще на ${(100 - totalInSom).toStringAsFixed(2)} сом',
                style: const TextStyle(color: Colors.white, fontSize: 13),
                textAlign: TextAlign.center,
              ),
            ),
        ],
      ),
    );
  }
}

class SummaryRow extends StatelessWidget {
  final String label;
  final String value;
  final bool bold;
  const SummaryRow(
      {super.key, required this.label, required this.value, this.bold = false});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: TextStyle(
                  color: Colors.white,
                  fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
          Text(value,
              style: TextStyle(
                  color: Colors.white,
                  fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
        ],
      ),
    );
  }
}
