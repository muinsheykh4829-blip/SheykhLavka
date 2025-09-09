import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/cart_model.dart';
import '../theme.dart';

class CartSummaryBar extends StatelessWidget {
  final VoidCallback onTap;
  const CartSummaryBar({super.key, required this.onTap});

  String _format(double v) {
    // Значения уже в сомах, конвертация не нужна
    final somValue = v;
    return '${somValue.toStringAsFixed(2)} с.'; // сомы с копейками
  }

  @override
  Widget build(BuildContext context) {
    final total = context.watch<CartModel>().total;
    final count = context.watch<CartModel>().count;

    if (total <= 0) return const SizedBox.shrink();

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: AppColors.primary,
          borderRadius: BorderRadius.circular(28),
          boxShadow: const [BoxShadow(blurRadius: 12, color: Colors.black26)],
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(_format(total),
                style: const TextStyle(
                    color: Colors.white, fontWeight: FontWeight.w800)),
            const SizedBox(width: 12),
            Container(width: 1, height: 18, color: Colors.white70),
            const SizedBox(width: 12),
            Text('$count',
                style: const TextStyle(
                    color: Colors.white, fontWeight: FontWeight.w700)),
          ],
        ),
      ),
    );
  }
}
