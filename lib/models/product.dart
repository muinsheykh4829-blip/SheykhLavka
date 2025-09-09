import '../config/api_config.dart';

class Product {
  final int id;
  final String name;
  final String nameRu;
  final String description;
  final String descriptionRu;
  final double price;
  final double? discountPrice;
  final String unit;
  final int categoryId;
  final List<String> images;
  final bool inStock;
  final bool isActive;
  final DateTime createdAt;
  final DateTime updatedAt;

  const Product({
    required this.id,
    required this.name,
    required this.nameRu,
    required this.description,
    required this.descriptionRu,
    required this.price,
    this.discountPrice,
    required this.unit,
    required this.categoryId,
    required this.images,
    this.inStock = true,
    this.isActive = true,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    // Собираем список изображений: API может вернуть images (array) или image (string)
    final List<String> rawImages = [];
    if (json['images'] != null) {
      try {
        rawImages.addAll(List<String>.from(json['images']));
      } catch (_) {}
    }
    if (json['image'] != null && json['image'].toString().isNotEmpty) {
      rawImages.add(json['image'].toString());
    }

    // Удаляем дубликаты
    final deduped = rawImages.toSet().toList();

    // Преобразуем относительные пути в абсолютные URL
    final normalized = deduped.map((p) {
      if (p.startsWith('http://') || p.startsWith('https://')) return p;
      if (p.startsWith('uploads/') ||
          p.startsWith('/uploads/') ||
          p.startsWith('storage/') ||
          p.startsWith('/storage/')) {
        final cleaned = p.startsWith('/') ? p.substring(1) : p;
        return ApiConfig.fileUrl(cleaned);
      }
      return p; // возможно это имя ассета
    }).toList();

    return Product(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      nameRu: json['name_ru'] ?? json['name'] ?? '',
      description: json['description'] ?? '',
      descriptionRu: json['description_ru'] ?? json['description'] ?? '',
      price:
          json['price'] != null ? double.parse(json['price'].toString()) : 0.0,
      discountPrice: json['discount_price'] != null
          ? double.parse(json['discount_price'].toString())
          : null,
      unit: json['unit'] ?? 'шт',
      categoryId: json['category_id'] ?? 0,
      images: normalized,
      inStock: json['in_stock'] ?? true,
      isActive: json['is_active'] ?? true,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : DateTime.now(),
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'])
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'name_ru': nameRu,
      'description': description,
      'description_ru': descriptionRu,
      'price': price,
      'discount_price': discountPrice,
      'unit': unit,
      'category_id': categoryId,
      'images': images,
      'in_stock': inStock,
      'is_active': isActive,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  // Получить локализованное название
  String getLocalizedName([String locale = 'ru']) {
    return locale == 'ru' ? nameRu : name;
  }

  // Получить локализованное описание
  String getLocalizedDescription([String locale = 'ru']) {
    return locale == 'ru' ? descriptionRu : description;
  }

  // Получить основное изображение
  String get primaryImage {
    return images.isNotEmpty ? images.first : '';
  }

  // Получить цену со скидкой или обычную
  double get currentPrice {
    return discountPrice ?? price;
  }

  // Цена уже в сомах, деление на 100 не нужно
  double get priceInSom {
    return price;
  }

  // Цена со скидкой уже в сомах
  double get currentPriceInSom {
    return currentPrice;
  }

  // Форматированная цена в сомах
  String get formattedPriceWithKopecks {
    return '${priceInSom.toStringAsFixed(2)} с';
  }

  // Форматированная текущая цена в сомах
  String get formattedCurrentPriceWithKopecks {
    return '${currentPriceInSom.toStringAsFixed(2)} с';
  }

  // Есть ли скидка
  bool get hasDiscount {
    return discountPrice != null && discountPrice! < price;
  }

  // Процент скидки
  int get discountPercentage {
    if (!hasDiscount) return 0;
    return ((price - discountPrice!) / price * 100).round();
  }

  // Проверка, является ли товар весовым
  bool get isWeightProduct {
    final lowercaseUnit = unit.toLowerCase();
    return lowercaseUnit.contains('кг') ||
        lowercaseUnit.contains('kg') ||
        lowercaseUnit.contains('г') ||
        lowercaseUnit.contains('gram') ||
        lowercaseUnit.contains('грамм');
  }
}
