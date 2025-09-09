import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/cart_model.dart';

class PaymentSelector extends StatefulWidget {
  final void Function(int)? onChanged;
  final int initial;
  const PaymentSelector({super.key, this.onChanged, this.initial = 0});

  @override
  State<PaymentSelector> createState() => _PaymentSelectorState();
}

class _PaymentSelectorState extends State<PaymentSelector> {
  late int selected;

  @override
  void initState() {
    super.initState();
    selected = widget.initial;
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartModel>();
    // синхронизация с моделью при первом построении
    if ((cart.payment == PaymentMethod.card && selected != 0) ||
        (cart.payment == PaymentMethod.cash && selected != 1)) {
      selected = cart.payment == PaymentMethod.card ? 0 : 1;
    }
    return Row(
      children: [
        Expanded(
          child: GestureDetector(
            onTap: () {
              setState(() => selected = 0);
              cart.setPayment(PaymentMethod.card);
              widget.onChanged?.call(0);
            },
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 12),
              decoration: BoxDecoration(
                color: selected == 0 ? const Color(0xFF22A447) : Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: selected == 0
                      ? const Color(0xFF22A447)
                      : Colors.grey.shade300,
                  width: 1.2,
                ),
              ),
              child: Center(
                child: Text(
                  'Карта',
                  style: TextStyle(
                    color: selected == 0 ? Colors.white : Colors.black87,
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                  ),
                ),
              ),
            ),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: GestureDetector(
            onTap: () {
              setState(() => selected = 1);
              cart.setPayment(PaymentMethod.cash);
              widget.onChanged?.call(1);
            },
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 12),
              decoration: BoxDecoration(
                color: selected == 1 ? const Color(0xFF22A447) : Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: selected == 1
                      ? const Color(0xFF22A447)
                      : Colors.grey.shade300,
                  width: 1.2,
                ),
              ),
              child: Center(
                child: Text(
                  'Наличные',
                  style: TextStyle(
                    color: selected == 1 ? Colors.white : Colors.black87,
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                  ),
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}
