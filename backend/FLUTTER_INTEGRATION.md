# Конфигурация для Flutter приложения

## Настройки API

### Базовый URL
```dart
const String baseUrl = 'http://127.0.0.1:8000/api/v1';
```

### Заголовки
```dart
Map<String, String> getHeaders({String? token}) {
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  };
}
```

### Примеры HTTP запросов для Flutter

#### Регистрация
```dart
Future<Map<String, dynamic>> register({
  required String name,
  required String phone,
  required String password,
}) async {
  final response = await http.post(
    Uri.parse('$baseUrl/auth/register'),
    headers: getHeaders(),
    body: json.encode({
      'name': name,
      'phone': phone,
      'password': password,
      'password_confirmation': password,
    }),
  );
  return json.decode(response.body);
}
```

#### Подтверждение кода
```dart
Future<Map<String, dynamic>> verifyCode({
  required int userId,
  required String code,
}) async {
  final response = await http.post(
    Uri.parse('$baseUrl/auth/verify-code'),
    headers: getHeaders(),
    body: json.encode({
      'user_id': userId,
      'code': code,
    }),
  );
  return json.decode(response.body);
}
```

#### Получение категорий
```dart
Future<List<Category>> getCategories() async {
  final response = await http.get(
    Uri.parse('$baseUrl/categories'),
    headers: getHeaders(),
  );
  
  final data = json.decode(response.body);
  if (data['success']) {
    return (data['data'] as List)
        .map((json) => Category.fromJson(json))
        .toList();
  }
  throw Exception('Failed to load categories');
}
```

#### Получение товаров
```dart
Future<Map<String, dynamic>> getProducts({
  int? categoryId,
  String? search,
  int page = 1,
}) async {
  final params = <String, String>{
    'page': page.toString(),
    if (categoryId != null) 'category_id': categoryId.toString(),
    if (search != null && search.isNotEmpty) 'search': search,
  };
  
  final uri = Uri.parse('$baseUrl/products').replace(queryParameters: params);
  final response = await http.get(uri, headers: getHeaders());
  
  return json.decode(response.body);
}
```

### Модели данных

#### Category
```dart
class Category {
  final int id;
  final String name;
  final String nameRu;
  final String slug;
  final String? icon;
  final bool isActive;

  Category({
    required this.id,
    required this.name,
    required this.nameRu,
    required this.slug,
    this.icon,
    required this.isActive,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'],
      name: json['name'],
      nameRu: json['name_ru'],
      slug: json['slug'],
      icon: json['icon'],
      isActive: json['is_active'],
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
  final bool isActive;
  final bool inStock;

  // Конструктор и fromJson методы...
}
```

### Хранение токена
```dart
import 'package:shared_preferences/shared_preferences.dart';

class TokenStorage {
  static const String _tokenKey = 'auth_token';
  
  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }
  
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }
  
  static Future<void> removeToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }
}
```

## Необходимые зависимости для Flutter

Добавьте в `pubspec.yaml`:
```yaml
dependencies:
  http: ^1.1.0
  shared_preferences: ^2.2.2
```
