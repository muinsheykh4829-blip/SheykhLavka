class Order {
  final int id;
  final String orderNumber;
  final String status;
  final String statusName;
  final double total;
  final String createdAt;
  final String deliveryAddress;
  final String deliveryPhone;
  final String? deliveryName;
  final String deliveryType;
  final String deliveryTypeName;
  final String? comment;
  final int itemsCount;
  final List<OrderItem> items;
  final String? completedBy;
  final String? completedAt;

  Order({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.statusName,
    required this.total,
    required this.createdAt,
    required this.deliveryAddress,
    required this.deliveryPhone,
    this.deliveryName,
    required this.deliveryType,
    required this.deliveryTypeName,
    this.comment,
    required this.itemsCount,
    required this.items,
    this.completedBy,
    this.completedAt,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'],
      orderNumber: json['order_number'],
      status: json['status'],
      statusName: json['status_name'],
      total: double.tryParse(json['total'].toString()) ?? 0.0,
      createdAt: json['created_at'],
      deliveryAddress: json['delivery_address'],
      deliveryPhone: json['delivery_phone'],
      deliveryName: json['delivery_name'],
      deliveryType: json['delivery_type'] ?? 'standard',
      deliveryTypeName: json['delivery_type_name'] ?? 'Стандарт (бесплатно)',
      comment: json['comment'],
      itemsCount: json['items_count'],
      completedBy: json['completed_by'],
      completedAt: json['completed_at'],
      items: (json['items'] as List)
          .map((item) => OrderItem.fromJson(item))
          .toList(),
    );
  }

  String get formatTotal => '${total.toStringAsFixed(0)} с';
}

class OrderItem {
  final int id;
  final String productName;
  final int quantity;
  final double? weight;
  final double price;
  final double total;
  final bool collected;
  final Product? product;

  OrderItem({
    required this.id,
    required this.productName,
    required this.quantity,
    this.weight,
    required this.price,
    required this.total,
    this.collected = false,
    this.product,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: json['id'],
      productName: json['product_name'],
      quantity: json['quantity'],
      weight: json['weight'] != null
          ? double.tryParse(json['weight'].toString())
          : null,
      price: double.tryParse(json['price'].toString()) ?? 0.0,
      total: double.tryParse(json['total'].toString()) ?? 0.0,
      collected: json['collected'] ?? false,
      product:
          json['product'] != null ? Product.fromJson(json['product']) : null,
    );
  }

  String get formatPrice => '${price.toStringAsFixed(0)} с';
  String get formatTotal => '${total.toStringAsFixed(0)} с';

  // Метод для отображения количества с учетом веса
  String get displayQuantity {
    if (weight != null && weight! > 0) {
      return '${weight!.toStringAsFixed(3)} кг';
    }
    return '$quantity шт.';
  }
}

class Product {
  final int id;
  final String? nameRu;
  final String? descriptionRu;
  final double price;
  final int categoryId;
  final String? imageUrl;

  Product({
    required this.id,
    this.nameRu,
    this.descriptionRu,
    required this.price,
    required this.categoryId,
    this.imageUrl,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      nameRu: json['name_ru'],
      descriptionRu: json['description_ru'],
      price: double.tryParse(json['price'].toString()) ?? 0.0,
      categoryId: json['category_id'],
      imageUrl: json['image_url'],
    );
  }
}
