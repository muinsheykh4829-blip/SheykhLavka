import 'package:flutter/material.dart';

/// Временная заглушка экрана отслеживания.
/// Позже здесь будет карта с перемещением курьера.
class TrackingStubScreen extends StatelessWidget {
  final String? orderNumber;
  const TrackingStubScreen({super.key, this.orderNumber});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(orderNumber != null
            ? 'Отслеживание заказа #$orderNumber'
            : 'Отслеживание заказа'),
        centerTitle: true,
      ),
      body: const _TrackingStubBody(),
    );
  }
}

class _TrackingStubBody extends StatelessWidget {
  const _TrackingStubBody();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.map_outlined, size: 72, color: Colors.grey.shade400),
          const SizedBox(height: 24),
          const Text(
            'Скоро здесь будет карта\nс перемещением курьера',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
          ),
          const SizedBox(height: 12),
          Text(
            'Экран в разработке',
            style: TextStyle(color: Colors.grey.shade600),
          ),
        ],
      ),
    );
  }
}

// Используйте TrackingStubScreen напрямую как заглушку.
