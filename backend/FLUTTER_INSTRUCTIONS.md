# Инструкции для Flutter разработчика

## 🎯 Готовый бэкенд - что нужно знать

### 📱 SMS Подтверждение
**В режиме разработки:**
- Код подтверждения для ВСЕХ пользователей: **123456**
- Код возвращается в ответе регистрации
- SMS не отправляются

**Пример регистрации:**
```dart
// 1. Регистрируем пользователя
final registerResponse = await http.post(
  Uri.parse('$baseUrl/auth/register'),
  headers: {'Content-Type': 'application/json'},
  body: json.encode({
    'name': 'Иван Петров',
    'phone': '+998901234567',
    'password': 'password123',
    'password_confirmation': 'password123',
  }),
);

// 2. Получаем user_id из ответа
final registerData = json.decode(registerResponse.body);
int userId = registerData['data']['user_id'];
// verification_code будет "123456"

// 3. Сразу подтверждаем код 123456
final verifyResponse = await http.post(
  Uri.parse('$baseUrl/auth/verify-code'),
  headers: {'Content-Type': 'application/json'},
  body: json.encode({
    'user_id': userId,
    'code': '123456', // Всегда используем этот код в разработке
  }),
);

// 4. Получаем токен авторизации
final verifyData = json.decode(verifyResponse.body);
String token = verifyData['data']['token'];
```

### 🔐 Авторизация
После подтверждения кода пользователь получает токен для API:

```dart
// Сохраняем токен
await TokenStorage.saveToken(token);

// Используем для запросов
final response = await http.get(
  Uri.parse('$baseUrl/profile'),
  headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  },
);
```

### 🛍️ Основные API endpoints

#### Каталог товаров (без авторизации)
```dart
// Категории
GET /api/v1/categories

// Товары с фильтрами
GET /api/v1/products?category_id=1&search=мука&page=1

// Конкретный товар
GET /api/v1/products/1

// Баннеры
GET /api/v1/banners
```

#### Корзина и заказы (требуют токен)
```dart
// Добавить в корзину
POST /api/v1/cart
{
  "product_id": 1,
  "quantity": 2
}

// Создать заказ
POST /api/v1/orders
{
  "address_id": 1,
  "delivery_time": "2025-09-03 15:00:00",
  "notes": "Домофон 123"
}
```

### 📊 Структура ответов
Все API возвращают единый формат:

**Успех:**
```json
{
  "success": true,
  "message": "Операция выполнена",
  "data": { ... }
}
```

**Ошибка:**
```json
{
  "success": false,
  "message": "Описание ошибки",
  "errors": { "field": ["error message"] }
}
```

### 🏗️ Модели для Flutter

#### User
```dart
class User {
  final int id;
  final String name;
  final String phone;
  final String? email;
  final String? firstName;
  final String? lastName;
  
  User({
    required this.id,
    required this.name,
    required this.phone,
    this.email,
    this.firstName,
    this.lastName,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      phone: json['phone'],
      email: json['email'],
      firstName: json['first_name'],
      lastName: json['last_name'],
    );
  }
}
```

#### Product
```dart
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

  Product({
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
    required this.inStock,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      name: json['name'],
      nameRu: json['name_ru'],
      description: json['description'],
      descriptionRu: json['description_ru'],
      price: double.parse(json['price'].toString()),
      discountPrice: json['discount_price'] != null 
          ? double.parse(json['discount_price'].toString()) 
          : null,
      unit: json['unit'],
      categoryId: json['category_id'],
      images: List<String>.from(json['images'] ?? []),
      inStock: json['in_stock'],
    );
  }
}
```

### 🗄️ База данных
Бэкенд содержит тестовые данные:
- 12 категорий товаров
- Товары с изображениями
- 6 баннеров для главной страницы

### 🔧 Настройки
- **Базовый URL:** `http://127.0.0.1:8000/api/v1`
- **CORS:** настроен для работы с мобильными приложениями
- **Токены:** безлимитное время жизни (отзывются при logout)

### ⚠️ Важные моменты
1. **Всегда используйте код 123456** для подтверждения в разработке
2. **Изображения товаров** находятся в `assets/` папке Flutter проекта
3. **Токен** сохраняйте в SharedPreferences
4. **Обработка ошибок** - проверяйте поле `success` в ответах

### 🚀 Готовые для тестирования функции
- ✅ Регистрация с фиксированным кодом 123456
- ✅ Авторизация по номеру телефона
- ✅ Каталог товаров с категориями
- ✅ Корзина пользователя
- ✅ Создание заказов
- ✅ Управление адресами доставки
- ✅ Профиль пользователя

---

**🎉 Бэкенд полностью готов к интеграции!**  
Используйте код **123456** для всех пользователей в процессе разработки.
