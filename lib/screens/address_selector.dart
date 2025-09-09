import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/cart_model.dart';
import 'address_screen.dart';

class AddressSelector extends StatefulWidget {
  const AddressSelector({super.key});

  @override
  State<AddressSelector> createState() => AddressSelectorState();
}

class AddressSelectorState extends State<AddressSelector> {
  @override
  Widget build(BuildContext context) {
    final address = context.watch<CartModel>().address;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Выберите адрес',
          style: TextStyle(fontSize: 13, color: Colors.black54),
        ),
        const SizedBox(height: 4),
        GestureDetector(
          onTap: () async {
            await Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const AddressScreen()),
            );
            setState(() {}); // обновить после выбора
          },
          child: Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
            decoration: BoxDecoration(
              color: const Color(0xFFF2F2F2),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              address.isEmpty ? 'Выберите адрес' : address,
              style: const TextStyle(fontSize: 15, color: Colors.black87),
            ),
          ),
        ),
      ],
    );
  }
}
