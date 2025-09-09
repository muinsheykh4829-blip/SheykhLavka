class CourierOrder {
  final int id;
  final String orderNumber;
  final String status;
  final double total;
  final DateTime createdAt;
  final Customer customer;
  final DeliveryAddress? address;
  final List<OrderItem> items;
  final CompletionInfo? completionInfo;
  final String deliveryType; // Тип доставки: standard/express
  final String paymentMethod; // Тип оплаты: cash/card

  CourierOrder({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.total,
    required this.createdAt,
    required this.customer,
    required this.address,
    required this.items,
    this.completionInfo,
    this.deliveryType = 'standard',
    this.paymentMethod = 'cash',
  });

  factory CourierOrder.fromJson(Map<String, dynamic> json) {
    try {
      // Подготовка адреса: либо объект address, либо плоские delivery_* поля
      DeliveryAddress? parsedAddress;
      final rawAddressObj = json['address'];
      if (rawAddressObj is Map) {
        parsedAddress =
            DeliveryAddress.fromJson(Map<String, dynamic>.from(rawAddressObj));
      } else if (json['delivery_address'] != null) {
        // Собираем из плоских полей
        parsedAddress = DeliveryAddress(
          address: json['delivery_address']?.toString() ?? '',
          house: json['delivery_house']?.toString(),
          entrance: json['delivery_entrance']?.toString(),
          floor: json['delivery_floor']?.toString(),
          apartment: json['delivery_apartment']?.toString(),
          comment: json['delivery_comment']?.toString(),
        );
      }
      return CourierOrder(
        id: json['id'] ?? 0,
        orderNumber: json['order_number']?.toString() ?? '',
        status: json['status']?.toString() ?? '',
        total: double.tryParse(json['total']?.toString() ?? '0') ?? 0.0,
        createdAt: json['created_at'] != null
            ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now()
            : DateTime.now(),
        customer: json['customer'] != null
            ? Customer.fromJson(json['customer'])
            : Customer(name: 'Неизвестно', phone: ''),
        address: parsedAddress,
        items: json['items'] != null
            ? (json['items'] as List)
                .map((item) => OrderItem.fromJson(item))
                .toList()
            : [],
        completionInfo: json['completion_info'] != null
            ? CompletionInfo.fromJson(json['completion_info'])
            : null,
        deliveryType: json['delivery_type']?.toString() ?? 'standard',
        paymentMethod: json['payment_method']?.toString() ?? 'cash',
      );
    } catch (e) {
      rethrow;
    }
  }

  String get statusName {
    switch (status) {
      case 'ready':
        return 'Готов к доставке';
      case 'delivering':
        return 'Доставляется';
      case 'delivered':
        return 'Доставлен';
      default:
        return status;
    }
  }

  String get deliveryTypeText {
    switch (deliveryType.toLowerCase()) {
      case 'express':
        return 'Экспресс';
      case 'standard':
      default:
        return 'Стандарт';
    }
  }

  String get paymentMethodText {
    switch (paymentMethod.toLowerCase()) {
      case 'card':
        return 'Картой';
      case 'cash':
      default:
        return 'Наличные';
    }
  }

  String get formattedTotal {
    // Преобразуем из дирамов в сомоны (делим на 100) и форматируем с двумя знаками после запятой
    double somAmount = total;
    return '${somAmount.toStringAsFixed(2)} с.';
  }
}

class Customer {
  final String name;
  final String phone;

  Customer({
    required this.name,
    required this.phone,
  });

  factory Customer.fromJson(Map<String, dynamic> json) {
    return Customer(
      name: json['name']?.toString() ?? 'Неизвестно',
      phone: json['phone']?.toString() ?? '',
    );
  }
}

class DeliveryAddress {
  final String address;
  final String? house; // дом
  final String? entrance;
  final String? floor;
  final String? apartment;
  final String? comment;

  DeliveryAddress({
    required this.address,
    this.house,
    this.entrance,
    this.floor,
    this.apartment,
    this.comment,
  });

  factory DeliveryAddress.fromJson(Map<String, dynamic> json) {
    String rawAddress = json['address'] ?? '';
    String? house = json['house'];
    // Если house не пришёл отдельно – пробуем вычленить из строки вида "... , дом 6а"
    String? entrance = json['entrance'];
    String? floor = json['floor'];
    String? apartment = json['apartment'];

    if ((house == null || house.toString().isEmpty)) {
      final regHouse = RegExp(r'(дом|д\.)\s*([\w\-]+)', caseSensitive: false);
      final m = regHouse.firstMatch(rawAddress);
      if (m != null) {
        house = m.group(2)?.trim();
        rawAddress = rawAddress
            .replaceFirst(m.group(0)!, '')
            .replaceAll(RegExp(r'\s{2,}'), ' ')
            .trim()
            .trim()
            .replaceAll(RegExp(r'^[,\s]+|[,\s]+$'), '');
      }
    }
    if ((entrance == null || entrance.isEmpty)) {
      final regEntrance =
          RegExp(r'(подъезд|под\.)\s*([\w\-]+)', caseSensitive: false);
      final m = regEntrance.firstMatch(rawAddress);
      if (m != null) {
        entrance = m.group(2)?.trim();
        rawAddress = rawAddress
            .replaceFirst(m.group(0)!, '')
            .replaceAll(RegExp(r'\s{2,}'), ' ')
            .trim()
            .replaceAll(RegExp(r'^[,\s]+|[,\s]+$'), '');
      }
    }
    if ((floor == null || floor.isEmpty)) {
      final regFloor = RegExp(r'(этаж|эт\.)\s*([\w\-]+)', caseSensitive: false);
      final m = regFloor.firstMatch(rawAddress);
      if (m != null) {
        floor = m.group(2)?.trim();
        rawAddress = rawAddress
            .replaceFirst(m.group(0)!, '')
            .replaceAll(RegExp(r'\s{2,}'), ' ')
            .trim()
            .replaceAll(RegExp(r'^[,\s]+|[,\s]+$'), '');
      }
    }
    if ((apartment == null || apartment.isEmpty)) {
      final regApt =
          RegExp(r'(кв\.|квартира)\s*([\w\-]+)', caseSensitive: false);
      final m = regApt.firstMatch(rawAddress);
      if (m != null) {
        apartment = m.group(2)?.trim();
        rawAddress = rawAddress
            .replaceFirst(m.group(0)!, '')
            .replaceAll(RegExp(r'\s{2,}'), ' ')
            .trim()
            .replaceAll(RegExp(r'^[,\s]+|[,\s]+$'), '');
      }
    }
    return DeliveryAddress(
      address: rawAddress,
      house: house,
      entrance: entrance,
      floor: floor,
      apartment: apartment,
      comment: json['comment'],
    );
  }

  String get fullAddress {
    String result = address;
    if (house != null && house!.isNotEmpty) result += ', дом $house';
    if (entrance != null && entrance!.isNotEmpty) {
      result += ', подъезд $entrance';
    }
    if (floor != null && floor!.isNotEmpty) result += ', этаж $floor';
    if (apartment != null && apartment!.isNotEmpty) {
      result += ', кв. $apartment';
    }
    return result;
  }
}

class OrderItem {
  final String productName;
  final double quantity; // Изменили на double для точного веса
  final double price;
  final String unit; // Единица измерения: 'kg' для весовых, 'pc' для штучных

  OrderItem({
    required this.productName,
    required this.quantity,
    required this.price,
    this.unit = 'pc', // По умолчанию штучный товар
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      productName: json['product_name'] ?? 'Неизвестный товар',
      quantity: double.tryParse(json['quantity']?.toString() ?? '1') ?? 1.0,
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      unit: json['unit']?.toString() ?? 'pc',
    );
  }

  String get formattedQuantity {
    // Форматируем количество в зависимости от типа товара
    if (unit == 'кг' || unit == 'kg') {
      // Для весовых товаров показываем с точностью до грамм
      return '${quantity.toStringAsFixed(3)} кг';
    } else if (unit == 'л' || unit == 'l' || unit == 'литр') {
      // Для жидкостей
      return '${quantity.toStringAsFixed(2)} л';
    } else {
      // Для штучных товаров показываем целое число
      return '${quantity.toInt()} шт';
    }
  }

  String get formattedPrice {
    // Преобразуем из дирамов в сомоны (делим на 100) и форматируем с двумя знаками после запятой
    double somAmount = price / 100;
    return '${somAmount.toStringAsFixed(2)} с.';
  }
}

class CompletionInfo {
  final String? completedBy;
  final DateTime completedAt;

  CompletionInfo({
    this.completedBy,
    required this.completedAt,
  });

  factory CompletionInfo.fromJson(Map<String, dynamic> json) {
    return CompletionInfo(
      completedBy: json['completed_by']?.toString(),
      completedAt:
          DateTime.tryParse(json['completed_at'].toString()) ?? DateTime.now(),
    );
  }
}
