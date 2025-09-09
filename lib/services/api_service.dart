import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';
import 'cache_service.dart';

class ApiService {
  // Используем конфигурацию из ApiConfig
  static String get baseUrl => ApiConfig.currentUrl;

  // Получить заголовки для запросов
  static Future<Map<String, String>> _getHeaders(
      {bool needsAuth = false}) async {
    Map<String, String> headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (needsAuth) {
      final token = await getToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }

    return headers;
  }

  // Сохранить токен
  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  // Получить токен
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  // Алиас для getToken
  static Future<String?> getStoredToken() async {
    return getToken();
  }

  // Удалить токен
  static Future<void> removeToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }

  // Helper функция для очистки ответа от префикса "---"
  static String _cleanResponse(String responseBody) {
    if (responseBody.startsWith('---')) {
      return responseBody.substring(3); // Удаляем первые 3 символа "---"
    }
    return responseBody;
  }

  // Helper функция для безопасного декодирования JSON
  static dynamic _safeJsonDecode(String responseBody) {
    final cleanBody = _cleanResponse(responseBody);
    if (cleanBody.trim().isEmpty) {
      return {
        'success': false,
        'message': 'Пустой ответ сервера',
      };
    }
    try {
      return json.decode(cleanBody);
    } catch (e) {
      print('⚠ JSON parse error: $e; body="$cleanBody"');
      return {
        'success': false,
        'message': 'Ошибка формата ответа',
        'raw': cleanBody.substring(
            0, cleanBody.length > 300 ? 300 : cleanBody.length)
      };
    }
  }

  // Проверить, авторизован ли пользователь
  static Future<bool> isAuthenticated() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  // АУТЕНТИФИКАЦИЯ

  // Регистрация пользователя
  static Future<Map<String, dynamic>> register({
    required String name,
    required String phone,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/register'),
        headers: await _getHeaders(),
        body: json.encode({
          'name': name,
          'phone': phone,
          'password': password,
          'password_confirmation': password,
        }),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка соединения: $e',
      };
    }
  }

  // Подтверждение SMS кода
  static Future<Map<String, dynamic>> verifyCode({
    required int userId,
    required String code,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/verify-code'),
        headers: await _getHeaders(),
        body: json.encode({
          'user_id': userId,
          'code': code,
        }),
      );

      final data = _safeJsonDecode(response.body);

      // Сохраняем токен при успешном подтверждении
      if (data['success'] == true && data['data']['token'] != null) {
        await saveToken(data['data']['token']);
      }

      return data;
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка соединения: $e',
      };
    }
  }

  // Отправка SMS для входа по номеру телефона
  static Future<Map<String, dynamic>> sendLoginSms({
    required String phone,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/send-login-sms'),
        headers: await _getHeaders(),
        body: json.encode({
          'phone': phone,
        }),
      );
      print(
          '➡ sendLoginSms -> ${response.statusCode} ${response.body.length}b');
      if (response.statusCode != 200) {
        print('⚠ Non-200 body: ${response.body}');
      }
      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка соединения: $e',
      };
    }
  }

  // Вход в систему
  static Future<Map<String, dynamic>> login({
    required String phone,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/login'),
        headers: await _getHeaders(),
        body: json.encode({
          'phone': phone,
          'password': password,
        }),
      );
      print('➡ login -> ${response.statusCode} ${response.body.length}b');
      if (response.statusCode != 200) {
        print('⚠ Non-200 body: ${response.body}');
      }
      final data = _safeJsonDecode(response.body);

      // Сохраняем токен при успешном входе
      if (data['success'] == true && data['data']['token'] != null) {
        await saveToken(data['data']['token']);
      }

      return data;
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка соединения: $e',
      };
    }
  }

  // Выход из системы
  static Future<Map<String, dynamic>> logout() async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/logout'),
        headers: await _getHeaders(needsAuth: true),
      );

      // Удаляем токен и очищаем кеш в любом случае
      await removeToken();
      await CacheService.clearCache();

      return _safeJsonDecode(response.body);
    } catch (e) {
      await removeToken();
      await CacheService.clearCache();
      return {
        'success': true,
        'message': 'Выход выполнен',
      };
    }
  }

  // Обновление профиля пользователя
  static Future<Map<String, dynamic>> updateProfile({
    required String firstName,
    required String lastName,
    required String gender,
    String? avatarPath,
  }) async {
    try {
      final body = {
        'first_name': firstName,
        'last_name': lastName,
        'gender': gender,
      };

      final response = await http.put(
        Uri.parse('$baseUrl/user/profile'),
        headers: await _getHeaders(needsAuth: true),
        body: json.encode(body),
      );

      final result = _safeJsonDecode(response.body);

      // Очищаем кеш профиля после обновления
      if (result['success'] == true) {
        await CacheService.clearProfileCache();
      }

      return result;
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка соединения: $e',
      };
    }
  }

  // Получение профиля пользователя
  static Future<Map<String, dynamic>> getProfile() async {
    // Сначала пытаемся получить из кеша
    final cachedData = await CacheService.getCachedProfile();
    if (cachedData != null) {
      return cachedData;
    }

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/user/profile'),
        headers: await _getHeaders(needsAuth: true),
      );

      if (response.statusCode == 200) {
        final data = _safeJsonDecode(response.body);
        if (data['success'] == true) {
          // Сохраняем в кеш
          await CacheService.setCachedProfile(data);
          return data;
        }
      }

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка соединения: $e',
      };
    }
  }

  // КАТАЛОГ ТОВАРОВ

  // Получить категории
  static Future<Map<String, dynamic>> getCategories() async {
    // Сначала пытаемся получить из кеша
    final cachedData = await CacheService.getCachedCategories();
    if (cachedData != null) {
      return cachedData;
    }

    // Если кеш пустой или устарел, загружаем с сервера
    try {
      final url = '$baseUrl/categories';
      print('🌐 Запрос категорий: $url');

      final response = await http
          .get(
            Uri.parse(url),
            headers: await _getHeaders(),
          )
          .timeout(const Duration(seconds: 30));

      print('📊 Статус ответа: ${response.statusCode}');
      print('📄 Тело ответа: ${response.body}');

      if (response.statusCode == 200) {
        final data = _safeJsonDecode(response.body);
        // Проверяем что data['data'] существует и является массивом
        if (data['success'] == true && data['data'] != null) {
          print('✅ Категории загружены: ${(data['data'] as List).length}');
          final result = {
            'success': true,
            'data': data['data'] is List ? data['data'] : []
          };

          // Сохраняем в кеш
          await CacheService.setCachedCategories(result);

          return result;
        } else {
          print('❌ Неверный формат данных: $data');
        }
      } else {
        print('❌ HTTP ошибка: ${response.statusCode} - ${response.body}');
      }

      return {
        'success': false,
        'message': 'Ошибка сервера: ${response.statusCode}',
        'data': []
      };
    } catch (e) {
      print('❌ Исключение при загрузке категорий: $e');
      return {
        'success': false,
        'message': 'Ошибка загрузки категорий: $e',
        'data': [] // Всегда возвращаем пустой массив при ошибке
      };
    }
  }

  // Получить товары
  static Future<Map<String, dynamic>> getProducts({
    int? categoryId,
    String? search,
    int page = 1,
  }) async {
    // Только для первой страницы используем кеш
    if (page == 1) {
      final cachedData = await CacheService.getCachedProducts(
        categoryId: categoryId?.toString(),
        search: search,
      );
      if (cachedData != null) {
        return cachedData;
      }
    }

    try {
      final params = <String, String>{
        'page': page.toString(),
      };

      if (categoryId != null) params['category_id'] = categoryId.toString();
      if (search != null && search.isNotEmpty) params['search'] = search;

      final uri =
          Uri.parse('$baseUrl/products').replace(queryParameters: params);

      final response = await http.get(
        uri,
        headers: await _getHeaders(),
      );

      if (response.statusCode == 200) {
        final data = _safeJsonDecode(response.body);
        // Проверяем что data['data'] существует и является массивом
        if (data['success'] == true && data['data'] != null) {
          final result = {
            'success': true,
            'data': data['data'] is List ? data['data'] : []
          };

          // Сохраняем в кеш только первую страницу
          if (page == 1) {
            await CacheService.setCachedProducts(
              result,
              categoryId: categoryId?.toString(),
              search: search,
            );
          }

          return result;
        }
      }

      return {
        'success': false,
        'message': 'Ошибка сервера: ${response.statusCode}',
        'data': []
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка загрузки товаров: $e',
        'data': []
      };
    }
  }

  // Получить товар по ID
  static Future<Map<String, dynamic>> getProduct(int productId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/products/$productId'),
        headers: await _getHeaders(),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка загрузки товара: $e',
      };
    }
  }

  // Получить баннеры
  static Future<Map<String, dynamic>> getBanners() async {
    print('🔄 Загрузка баннеров...');
    print('📍 URL: $baseUrl/banners');

    // Для отладки - пропускаем кеш
    // final cachedData = await CacheService.getCachedBanners();
    // if (cachedData != null) {
    //   print('📦 Возвращаем данные из кеша');
    //   return cachedData;
    // }

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/banners'),
        headers: await _getHeaders(),
      );

      print('📊 Статус ответа: ${response.statusCode}');
      print('📄 Тело ответа: ${response.body}');

      if (response.statusCode == 200) {
        final data = _safeJsonDecode(response.body);
        if (data['success'] == true) {
          print(
              '✅ Баннеры успешно загружены: ${data['data']?.length ?? 0} шт.');
          // Сохраняем в кеш
          await CacheService.setCachedBanners(data);
          return data;
        }
      }

      return _safeJsonDecode(response.body);
    } catch (e) {
      print('❌ Ошибка загрузки баннеров: $e');
      return {
        'success': false,
        'message': 'Ошибка загрузки баннеров: $e',
        'data': []
      };
    }
  }

  // Регистрация клика по баннеру
  static Future<Map<String, dynamic>> registerBannerClick(int bannerId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/banners/$bannerId/click'),
        headers: await _getHeaders(),
      );

      final data = _safeJsonDecode(response.body);
      return data;
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка регистрации клика: $e',
      };
    }
  }

  // КОРЗИНА (требует авторизации)

  // Получить корзину
  static Future<Map<String, dynamic>> getCart() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/cart'),
        headers: await _getHeaders(needsAuth: true),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка загрузки корзины: $e',
      };
    }
  }

  // Добавить товар в корзину
  static Future<Map<String, dynamic>> addToCart({
    required int productId,
    required int quantity,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/cart'),
        headers: await _getHeaders(needsAuth: true),
        body: json.encode({
          'product_id': productId,
          'quantity': quantity,
        }),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка добавления в корзину: $e',
      };
    }
  }

  // Обновить количество товара в корзине
  static Future<Map<String, dynamic>> updateCartItem({
    required int cartItemId,
    required int quantity,
  }) async {
    try {
      final response = await http.put(
        Uri.parse('$baseUrl/cart/$cartItemId'),
        headers: await _getHeaders(needsAuth: true),
        body: json.encode({
          'quantity': quantity,
        }),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка обновления корзины: $e',
      };
    }
  }

  // Удалить товар из корзины
  static Future<Map<String, dynamic>> removeFromCart(int cartItemId) async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/cart/$cartItemId'),
        headers: await _getHeaders(needsAuth: true),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка удаления из корзины: $e',
      };
    }
  }

  // ================== АДРЕСА ==================

  // Получить все адреса пользователя
  static Future<Map<String, dynamic>> getAddresses() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/addresses'),
        headers: await _getHeaders(needsAuth: true),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка получения адресов: $e',
      };
    }
  }

  // Добавить новый адрес
  static Future<Map<String, dynamic>> addAddress({
    required String street,
    required String houseNumber,
    String? apartment,
    String? entrance,
    String? floor,
    String? intercom,
    String? city,
    String? district,
    String? comment,
    String? type,
    String? title,
    bool isDefault = false,
  }) async {
    try {
      final data = {
        'street': street,
        'house_number': houseNumber,
        'apartment': apartment,
        'entrance': entrance,
        'floor': floor,
        'intercom': intercom,
        'city': city ?? 'Ташкент',
        'district': district,
        'comment': comment,
        'type': type ?? 'home',
        'title': title,
        'is_default': isDefault,
      };

      // Удаляем null значения
      data.removeWhere((key, value) => value == null);

      final response = await http.post(
        Uri.parse('$baseUrl/addresses'),
        headers: await _getHeaders(needsAuth: true),
        body: json.encode(data),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка добавления адреса: $e',
      };
    }
  }

  // Обновить адрес
  static Future<Map<String, dynamic>> updateAddress({
    required int id,
    String? street,
    String? houseNumber,
    String? apartment,
    String? entrance,
    String? floor,
    String? intercom,
    String? city,
    String? district,
    String? comment,
    String? type,
    String? title,
    bool? isDefault,
  }) async {
    try {
      final data = <String, dynamic>{};

      if (street != null) data['street'] = street;
      if (houseNumber != null) data['house_number'] = houseNumber;
      if (apartment != null) data['apartment'] = apartment;
      if (entrance != null) data['entrance'] = entrance;
      if (floor != null) data['floor'] = floor;
      if (intercom != null) data['intercom'] = intercom;
      if (city != null) data['city'] = city;
      if (district != null) data['district'] = district;
      if (comment != null) data['comment'] = comment;
      if (type != null) data['type'] = type;
      if (title != null) data['title'] = title;
      if (isDefault != null) data['is_default'] = isDefault;

      final response = await http.put(
        Uri.parse('$baseUrl/addresses/$id'),
        headers: await _getHeaders(needsAuth: true),
        body: json.encode(data),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка обновления адреса: $e',
      };
    }
  }

  // Удалить адрес
  static Future<Map<String, dynamic>> deleteAddress(int id) async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/addresses/$id'),
        headers: await _getHeaders(needsAuth: true),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка удаления адреса: $e',
      };
    }
  }

  // Установить адрес по умолчанию
  static Future<Map<String, dynamic>> setDefaultAddress(int id) async {
    try {
      final response = await http.put(
        Uri.parse('$baseUrl/addresses/$id/default'),
        headers: await _getHeaders(needsAuth: true),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка установки адреса по умолчанию: $e',
      };
    }
  }

  // ===== ЗАКАЗЫ =====

  // Получить заказы пользователя
  static Future<Map<String, dynamic>> getOrders() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/orders'),
        headers: await _getHeaders(needsAuth: true),
      );

      if (response.statusCode == 200) {
        final data = _safeJsonDecode(response.body);
        return {
          'success': data['success'] ?? false,
          'orders': data['orders'] ?? []
        };
      }

      return {
        'success': false,
        'message': 'Ошибка сервера: ${response.statusCode}',
        'orders': []
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка загрузки заказов: $e',
        'orders': []
      };
    }
  }

  // Создать новый заказ
  static Future<Map<String, dynamic>> createOrder({
    required String deliveryAddress,
    required String deliveryPhone,
    String? deliveryName,
    String? deliveryTime,
    String? paymentMethod,
    String? comment,
    String? deliveryType, // Добавляем параметр для типа доставки
    List<Map<String, dynamic>>? items, // Добавляем параметр для товаров
  }) async {
    try {
      print('🛒 Создание заказа...');

      final requestData = {
        'delivery_address': deliveryAddress,
        'delivery_phone': deliveryPhone,
        'delivery_name': deliveryName,
        'delivery_time': deliveryTime,
        'payment_method': paymentMethod ?? 'cash',
        'comment': comment,
        'delivery_type': deliveryType ?? 'standard', // Добавляем тип доставки
        if (items != null) 'items': items, // Добавляем товары если они есть
      };

      print('📦 Данные заказа: $requestData');
      print('🚚 Тип доставки: ${deliveryType ?? 'standard'}');

      final response = await http
          .post(
            Uri.parse('$baseUrl/orders'),
            headers: await _getHeaders(needsAuth: true),
            body: json.encode(requestData),
          )
          .timeout(const Duration(seconds: 30));

      print('📊 Статус ответа: ${response.statusCode}');
      print('📄 Тело ответа: ${response.body}');

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = _safeJsonDecode(response.body);

        if (data['success'] == true) {
          print('✅ Заказ создан успешно: ${data['order']?['order_number']}');
          return {
            'success': true,
            'message': 'Заказ успешно оформлен!',
            'order': data['order']
          };
        } else {
          print('❌ Ошибка создания заказа: ${data['message']}');
        }
      }

      return _safeJsonDecode(response.body);
    } catch (e) {
      print('❌ Исключение при создании заказа: $e');
      return {
        'success': false,
        'message': 'Ошибка создания заказа: $e',
      };
    }
  }

  // Получить детали заказа
  static Future<Map<String, dynamic>> getOrderDetails(int orderId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/orders/$orderId'),
        headers: await _getHeaders(needsAuth: true),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка загрузки деталей заказа: $e',
      };
    }
  }

  // Отменить заказ
  static Future<Map<String, dynamic>> cancelOrder(int orderId) async {
    try {
      final response = await http.put(
        Uri.parse('$baseUrl/orders/$orderId/cancel'),
        headers: await _getHeaders(needsAuth: true),
      );

      return _safeJsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Ошибка отмены заказа: $e',
      };
    }
  }

  // МЕТОДЫ УПРАВЛЕНИЯ КЕШЕМ

  // Очистить весь кеш
  static Future<void> clearAllCache() async {
    await CacheService.clearCache();
  }

  // Очистить кеш товаров (при обновлении каталога)
  static Future<void> clearProductsCache() async {
    await CacheService.clearProductsCache();
  }

  // Очистить кеш профиля
  static Future<void> clearProfileCache() async {
    await CacheService.clearProfileCache();
  }

  // Получить статистику кеша
  static Future<Map<String, dynamic>> getCacheStats() async {
    return await CacheService.getCacheStats();
  }
}
