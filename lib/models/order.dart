import 'package:flutter/material.dart';

class Order {
  final int id;
  final String orderNumber;
  final int userId;
  final String status;
  final double subtotal;
  final double deliveryFee;
  final double discount;
  final double total;
  final String paymentMethod;
  final String paymentStatus;
  final String deliveryAddress;
  final String deliveryPhone;
  final String? deliveryName;
  final String? deliveryTime;
  final String? deliveryType;
  final String? comment;
  final DateTime createdAt;
  final List<OrderItem> items;

  Order({
    required this.id,
    required this.orderNumber,
    required this.userId,
    required this.status,
    required this.subtotal,
    required this.deliveryFee,
    required this.discount,
    required this.total,
    required this.paymentMethod,
    required this.paymentStatus,
    required this.deliveryAddress,
    required this.deliveryPhone,
    this.deliveryName,
    this.deliveryTime,
    this.deliveryType,
    this.comment,
    required this.createdAt,
    required this.items,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'],
      orderNumber: json['order_number'],
      userId: json['user_id'],
      status: json['status'],
      subtotal: double.tryParse(json['subtotal'].toString()) ?? 0.0,
      deliveryFee: double.tryParse(json['delivery_fee'].toString()) ?? 0.0,
      discount: double.tryParse(json['discount'].toString()) ?? 0.0,
      total: double.tryParse(json['total'].toString()) ?? 0.0,
      paymentMethod: json['payment_method'],
      paymentStatus: json['payment_status'],
      deliveryAddress: json['delivery_address'],
      deliveryPhone: json['delivery_phone'],
      deliveryName: json['delivery_name'],
      deliveryTime: json['delivery_time'],
      deliveryType: json['delivery_type'],
      comment: json['comment'],
      createdAt: DateTime.parse(json['created_at']),
      items: (json['items'] as List)
          .map((item) => OrderItem.fromJson(item))
          .toList(),
    );
  }

  // Получить локализованное название статуса
  String get statusName {
    switch (status) {
      case 'processing':
        return 'В обработке';
      case 'accepted':
        return 'Принят';
      case 'preparing':
        return 'Собирается';
      case 'ready':
        return 'Собран';
      case 'delivering':
        return 'Курьер в пути';
      case 'delivered':
        return 'Завершен';
      case 'cancelled':
        return 'Отменен';
      default:
        return status;
    }
  }

  // Получить цвет статуса
  Color get statusColor {
    switch (status) {
      case 'processing':
        return Colors.orange;
      case 'accepted':
        return Colors.blue;
      case 'preparing':
        return Colors.purple;
      case 'ready':
        return Colors.teal;
      case 'delivering':
        return Colors.indigo;
      case 'delivered':
        return Colors.green;
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}

class OrderItem {
  final int id;
  final int productId;
  final int quantity;
  final double? weight; // вес для весовых товаров (кг)
  final double price;
  final double total;
  final Map<String, dynamic> product;

  OrderItem({
    required this.id,
    required this.productId,
    required this.quantity,
    this.weight,
    required this.price,
    required this.total,
    required this.product,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: json['id'],
      productId: json['product_id'],
      quantity: json['quantity'],
      weight: json['weight'] != null && json['weight'].toString().isNotEmpty
          ? double.tryParse(json['weight'].toString())
          : null,
      price: double.tryParse(json['price'].toString()) ?? 0.0,
      total: double.tryParse(json['total'].toString()) ?? 0.0,
      product: json['product'],
    );
  }
}
