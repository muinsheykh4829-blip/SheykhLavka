import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/cart_model.dart';

class CartItemTile extends StatelessWidget {
  final CartItem item;
  const CartItemTile({super.key, required this.item});

  @override
  Widget build(BuildContext context) {
    final cart = context.read<CartModel>();
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: item.product.images.isNotEmpty
                  ? Image.network(
                      item.product.images.first,
                      width: 64,
                      height: 64,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) => Container(
                        width: 64,
                        height: 64,
                        color: Colors.grey[200],
                        child: const Icon(Icons.image_not_supported),
                      ),
                    )
                  : Container(
                      width: 64,
                      height: 64,
                      color: Colors.grey[200],
                      child: const Icon(Icons.image_not_supported),
                    ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(item.product.nameRu,
                      style: const TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 2),
                  if (item.product.descriptionRu.isNotEmpty)
                    Text(item.product.descriptionRu,
                        style: const TextStyle(color: Color(0xFF6B6B6B))),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      // Проверяем, является ли товар весовым
                      if (item.isWeightProduct) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.grey[100],
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.grey[300]!),
                          ),
                          child: Text(
                            item.displayQuantity,
                            style: const TextStyle(
                              fontWeight: FontWeight.w700,
                              color: Colors.black87,
                            ),
                          ),
                        ),
                        const Spacer(),
                        Text(item.formattedTotalWithKopecks,
                            style:
                                const TextStyle(fontWeight: FontWeight.w800)),
                      ] else ...[
                        QtyBtn(
                            icon: Icons.remove,
                            onTap: () => cart.dec(item.product)),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12.0),
                          child: Text(item.displayQuantity,
                              style:
                                  const TextStyle(fontWeight: FontWeight.w700)),
                        ),
                        QtyBtn(
                            icon: Icons.add,
                            onTap: () => cart.inc(item.product)),
                        const Spacer(),
                        Text(item.formattedTotalWithKopecks,
                            style:
                                const TextStyle(fontWeight: FontWeight.w800)),
                      ],
                    ],
                  )
                ],
              ),
            )
          ],
        ),
      ),
    );
  }
}

class QtyBtn extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  const QtyBtn({super.key, required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Ink(
      decoration: BoxDecoration(
        border: Border.all(color: Colors.black12),
        borderRadius: BorderRadius.circular(10),
        color: Colors.white,
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(10),
        child: Padding(
          padding: const EdgeInsets.all(6.0),
          child: Icon(icon, size: 18),
        ),
      ),
    );
  }
}

class PaymentSheet extends StatelessWidget {
  const PaymentSheet({super.key});

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Container(
        decoration: const BoxDecoration(
          color: Color(0xFFF7F7F9),
          borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 48,
              height: 5,
              margin: const EdgeInsets.only(top: 8, bottom: 16),
              decoration: BoxDecoration(
                color: Colors.black12,
                borderRadius: BorderRadius.circular(4),
              ),
            ),
            ListTile(
              leading: const Icon(Icons.credit_card),
              title: const Text('Карта'),
              onTap: () => Navigator.pop(context, PaymentMethod.card),
            ),
            ListTile(
              leading: const Icon(Icons.payments_outlined),
              title: const Text('При получении'),
              onTap: () => Navigator.pop(context, PaymentMethod.cash),
            ),
          ],
        ),
      ),
    );
  }
}
