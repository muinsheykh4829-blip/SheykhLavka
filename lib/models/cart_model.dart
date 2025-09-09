import 'package:flutter/foundation.dart';
import 'product.dart';

enum PaymentMethod { card, cash }

enum DeliveryType { standart, express }

class CartItem {
  final Product product;
  int qty;
  double? weight; // для весовых товаров в кг

  CartItem({
    required this.product,
    this.qty = 1,
    this.weight,
  });

  double get total {
    if (weight != null) {
      // Для весовых товаров: цена за кг * вес в кг
      return product.price * weight!;
    } else {
      // Для штучных товаров: цена * количество
      return product.price * qty;
    }
  }

  // Общая сумма уже в сомах
  double get totalInSom {
    return total;
  }

  // Форматированная общая стоимость в сомах
  String get formattedTotalWithKopecks {
    return '${totalInSom.toStringAsFixed(2)} с';
  }

  bool get isWeightProduct => weight != null;

  String get displayQuantity {
    if (weight != null) {
      if (weight! >= 1) {
        return '${weight!.toStringAsFixed(3)} кг';
      } else {
        return '${(weight! * 1000).toStringAsFixed(0)} г';
      }
    } else {
      return '$qty шт';
    }
  }
}

class CartModel extends ChangeNotifier {
  final List<CartItem> _items = [];
  PaymentMethod payment = PaymentMethod.cash;
  String address =
      ''; // адрес по умолчанию пуст, чтобы кнопка оформления была неактивна
  double discount = 0;
  DeliveryType deliveryType = DeliveryType.standart;

  // Минимальная сумма заказа в сомах (без доставки)
  static const double minimumOrderAmount = 100.0;
  int get deliveryCost => deliveryType == DeliveryType.express ? 10 : 0;
  String get deliveryText =>
      deliveryType == DeliveryType.express ? '10 с' : 'бесплатный';
  void setDeliveryType(DeliveryType type) {
    deliveryType = type;
    notifyListeners();
  }

  List<CartItem> get items => List.unmodifiable(_items);
  int get count => _items.fold(0, (acc, e) => acc + e.qty);
  double get total => _items.fold(0.0, (acc, e) => acc + e.total);

  // Общая стоимость уже в сомах
  double get totalInSomWithKopecks => total;

  // Стоимость доставки уже в сомах
  double get deliveryCostInSomWithKopecks => deliveryCost.toDouble();

  // Итоговая сумма с доставкой в сомах
  double get finalTotalInSomWithKopecks =>
      totalInSomWithKopecks + deliveryCostInSomWithKopecks;

  // Форматированная общая сумма с копейками
  String get formattedTotalWithKopecks =>
      '${totalInSomWithKopecks.toStringAsFixed(2)} сом';

  // Форматированная итоговая сумма с доставкой с копейками
  String get formattedFinalTotalWithKopecks =>
      '${finalTotalInSomWithKopecks.toStringAsFixed(2)} сом';

  // Проверка минимальной суммы заказа (без учета доставки)
  bool get isMinimumOrderReached => totalInSomWithKopecks >= minimumOrderAmount;

  // Сколько не хватает до минимальной суммы
  double get amountToMinimum => isMinimumOrderReached
      ? 0.0
      : (minimumOrderAmount - totalInSomWithKopecks);

  // Текст о минимальной сумме
  String get minimumOrderText => isMinimumOrderReached
      ? ''
      : 'Минимальная сумма заказа ${minimumOrderAmount.toStringAsFixed(0)} сом. Добавьте еще на ${amountToMinimum.toStringAsFixed(2)} сом';

  // Ранее использовалась модель "дирамы" (100 = 1 сом). Теперь все цены сразу в сомах.
  // Если снова понадобится конверсия в минорные единицы, можно вернуть геттер с умножением.

  void add(Product p, {double? weight}) {
    final i = _items.indexWhere((e) => e.product.id == p.id);
    if (i >= 0) {
      if (weight != null) {
        // Для весовых товаров заменяем вес
        _items[i].weight = weight;
      } else {
        // Для штучных товаров увеличиваем количество
        _items[i].qty++;
      }
    } else {
      _items.add(CartItem(product: p, weight: weight));
    }
    notifyListeners();
  }

  void inc(Product p) {
    final i = _items.indexWhere((e) => e.product.id == p.id);
    if (i >= 0) {
      if (_items[i].isWeightProduct) {
        // Для весовых товаров увеличиваем вес на 0.1 кг
        _items[i].weight = (_items[i].weight! + 0.1).clamp(0.1, 99.9);
      } else {
        // Для штучных товаров увеличиваем количество
        _items[i].qty++;
      }
      notifyListeners();
    }
  }

  void dec(Product p) {
    final i = _items.indexWhere((e) => e.product.id == p.id);
    if (i >= 0) {
      if (_items[i].isWeightProduct) {
        // Для весовых товаров уменьшаем вес на 0.1 кг
        final newWeight = _items[i].weight! - 0.1;
        if (newWeight <= 0.05) {
          _items.removeAt(i);
        } else {
          _items[i].weight = newWeight;
        }
      } else {
        // Для штучных товаров уменьшаем количество
        _items[i].qty--;
        if (_items[i].qty <= 0) _items.removeAt(i);
      }
      notifyListeners();
    }
  }

  void setPayment(PaymentMethod m) {
    payment = m;
    notifyListeners();
  }

  void updateWeight(Product p, double newWeight) {
    final i = _items.indexWhere((e) => e.product.id == p.id);
    if (i >= 0 && _items[i].isWeightProduct) {
      _items[i].weight = newWeight;
      notifyListeners();
    }
  }

  void removeItem(Product p) {
    _items.removeWhere((e) => e.product.id == p.id);
    notifyListeners();
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }

  void setAddress(String a) {
    address = a;
    notifyListeners();
  }
}
