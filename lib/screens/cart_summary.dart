import 'package:flutter/material.dart';
import 'summary_row.dart';

class CartSummary extends StatelessWidget {
  final double total;
  final int delivery;
  const CartSummary({super.key, required this.total, required this.delivery});

  @override
  Widget build(BuildContext context) {
    const discount = 0;
    final sum = total + delivery - discount;
    return Container(
      margin: const EdgeInsets.only(top: 8),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          SummaryRow(
              label: 'Промежуточный итог',
              value: '${total.toStringAsFixed(0)} с'),
          SummaryRow(
              label: 'Доставка',
              value: delivery == 0 ? 'бесплатный' : '$delivery с'),
          const SizedBox(height: 4),
          SummaryRow(
              label: 'Итого', value: '${sum.toStringAsFixed(0)} с', bold: true),
        ],
      ),
    );
  }
}
