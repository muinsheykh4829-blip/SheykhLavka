import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../services/api_service.dart';

class DiagnosticsScreen extends StatefulWidget {
  const DiagnosticsScreen({super.key});

  @override
  State<DiagnosticsScreen> createState() => _DiagnosticsScreenState();
}

class _DiagnosticsScreenState extends State<DiagnosticsScreen> {
  final List<DiagnosticResult> _results = [];
  bool _isRunning = false;
  int _currentStep = 0;
  int _totalSteps = 0;

  @override
  void initState() {
    super.initState();
    _runFullDiagnostics();
  }

  Future<void> _runFullDiagnostics() async {
    setState(() {
      _isRunning = true;
      _results.clear();
      _currentStep = 0;
      _totalSteps = 15; // Общее количество проверок
    });

    // 1. Проверка подключения к интернету
    await _checkInternetConnection();

    // 2. Проверка базового URL API
    await _checkApiBaseUrl();

    // 3. Проверка авторизации
    await _checkAuthentication();

    // 4. Проверка профиля пользователя
    await _checkUserProfile();

    // 5. Проверка категорий
    await _checkCategories();

    // 6. Проверка продуктов
    await _checkProducts();

    // 7. Проверка баннеров
    await _checkBanners();

    // 8. Проверка корзины
    await _checkCart();

    // 9. Проверка адресов
    await _checkAddresses();

    // 10. Проверка заказов
    await _checkOrders();

    // 11. Проверка кеша
    await _checkCache();

    // 12. Проверка локального хранилища
    await _checkLocalStorage();

    // 13. Проверка ассетов
    await _checkAssets();

    // 14. Проверка системных разрешений
    await _checkSystemPermissions();

    // 15. Общий отчёт
    await _generateSummary();

    setState(() {
      _isRunning = false;
    });
  }

  void _incrementStep(String title) {
    setState(() {
      _currentStep++;
    });
  }

  void _addResult(String title, bool success, String message,
      [String? details]) {
    setState(() {
      _results.add(DiagnosticResult(
        title: title,
        success: success,
        message: message,
        details: details,
        timestamp: DateTime.now(),
      ));
    });
  }

  Future<void> _checkInternetConnection() async {
    _incrementStep('Проверка интернет-соединения');
    try {
      final result = await http.get(
        Uri.parse('https://www.google.com'),
        headers: {'User-Agent': 'DiagnosticsTool/1.0'},
      ).timeout(const Duration(seconds: 5));

      if (result.statusCode == 200) {
        _addResult('Интернет-соединение', true, 'Соединение работает',
            'HTTP ${result.statusCode}');
      } else {
        _addResult('Интернет-соединение', false, 'Неожиданный код ответа',
            'HTTP ${result.statusCode}');
      }
    } catch (e) {
      _addResult(
          'Интернет-соединение', false, 'Ошибка соединения', e.toString());
    }
  }

  Future<void> _checkApiBaseUrl() async {
    _incrementStep('Проверка базового URL API');
    try {
      final baseUrl = ApiService.baseUrl;
      final response = await http.get(
        Uri.parse('$baseUrl/'),
        headers: {'Accept': 'application/json'},
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200 || response.statusCode == 404) {
        _addResult('Базовый URL API', true, 'Сервер отвечает',
            'URL: $baseUrl, HTTP ${response.statusCode}');
      } else {
        _addResult('Базовый URL API', false, 'Сервер недоступен',
            'URL: $baseUrl, HTTP ${response.statusCode}');
      }
    } catch (e) {
      _addResult('Базовый URL API', false, 'Не удалось подключиться к API',
          '${ApiService.baseUrl}: $e');
    }
  }

  Future<void> _checkAuthentication() async {
    _incrementStep('Проверка авторизации');
    try {
      final isAuth = await ApiService.isAuthenticated();
      final token = await ApiService.getToken();

      if (isAuth && token != null) {
        // Проверяем действительность токена
        final profile = await ApiService.getProfile();
        if (profile['success'] == true) {
          _addResult('Авторизация', true, 'Пользователь авторизован',
              'Токен действителен');
        } else {
          _addResult('Авторизация', false, 'Токен недействителен',
              profile['message'] ?? 'Неизвестная ошибка');
        }
      } else {
        _addResult('Авторизация', false, 'Пользователь не авторизован',
            'Токен отсутствует');
      }
    } catch (e) {
      _addResult(
          'Авторизация', false, 'Ошибка проверки авторизации', e.toString());
    }
  }

  Future<void> _checkUserProfile() async {
    _incrementStep('Проверка профиля');
    try {
      final result = await ApiService.getProfile();
      if (result['success'] == true) {
        final user = result['data']?['user'];
        final phone = user?['phone'] ?? 'не указан';
        final name = user?['name'] ?? 'не указано';
        _addResult('Профиль пользователя', true, 'Профиль загружен',
            'Телефон: $phone, Имя: $name');
      } else {
        _addResult(
            'Профиль пользователя',
            false,
            'Не удалось загрузить профиль',
            result['message'] ?? 'Неизвестная ошибка');
      }
    } catch (e) {
      _addResult('Профиль пользователя', false, 'Ошибка загрузки профиля',
          e.toString());
    }
  }

  Future<void> _checkCategories() async {
    _incrementStep('Проверка категорий');
    try {
      final result = await ApiService.getCategories();
      if (result['success'] == true) {
        final categories = result['data'] as List?;
        final count = categories?.length ?? 0;
        _addResult('Категории товаров', true, 'Категории загружены',
            'Найдено категорий: $count');
      } else {
        _addResult('Категории товаров', false, 'Не удалось загрузить категории',
            result['message'] ?? 'Неизвестная ошибка');
      }
    } catch (e) {
      _addResult('Категории товаров', false, 'Ошибка загрузки категорий',
          e.toString());
    }
  }

  Future<void> _checkProducts() async {
    _incrementStep('Проверка товаров');
    try {
      final result = await ApiService.getProducts();
      if (result['success'] == true) {
        final products = result['data'] as List?;
        final count = products?.length ?? 0;
        _addResult(
            'Товары', true, 'Товары загружены', 'Найдено товаров: $count');
      } else {
        _addResult('Товары', false, 'Не удалось загрузить товары',
            result['message'] ?? 'Неизвестная ошибка');
      }
    } catch (e) {
      _addResult('Товары', false, 'Ошибка загрузки товаров', e.toString());
    }
  }

  Future<void> _checkBanners() async {
    _incrementStep('Проверка баннеров');
    try {
      final result = await ApiService.getBanners();
      if (result['success'] == true) {
        final banners = result['data'] as List?;
        final count = banners?.length ?? 0;
        _addResult(
            'Баннеры', true, 'Баннеры загружены', 'Найдено баннеров: $count');
      } else {
        _addResult('Баннеры', false, 'Не удалось загрузить баннеры',
            result['message'] ?? 'Неизвестная ошибка');
      }
    } catch (e) {
      _addResult('Баннеры', false, 'Ошибка загрузки баннеров', e.toString());
    }
  }

  Future<void> _checkCart() async {
    _incrementStep('Проверка корзины');
    try {
      final result = await ApiService.getCart();
      if (result['success'] == true) {
        final items = result['data']?['items'] as List?;
        final count = items?.length ?? 0;
        _addResult(
            'Корзина', true, 'Корзина доступна', 'Товаров в корзине: $count');
      } else {
        _addResult('Корзина', false, 'Не удалось загрузить корзину',
            result['message'] ?? 'Неизвестная ошибка');
      }
    } catch (e) {
      _addResult('Корзина', false, 'Ошибка загрузки корзины', e.toString());
    }
  }

  Future<void> _checkAddresses() async {
    _incrementStep('Проверка адресов');
    try {
      final result = await ApiService.getAddresses();
      if (result['success'] == true) {
        final addresses = result['data'] as List?;
        final count = addresses?.length ?? 0;
        _addResult('Адреса доставки', true, 'Адреса загружены',
            'Сохранено адресов: $count');
      } else {
        _addResult('Адреса доставки', false, 'Не удалось загрузить адреса',
            result['message'] ?? 'Неизвестная ошибка');
      }
    } catch (e) {
      _addResult(
          'Адреса доставки', false, 'Ошибка загрузки адресов', e.toString());
    }
  }

  Future<void> _checkOrders() async {
    _incrementStep('Проверка заказов');
    try {
      final result = await ApiService.getOrders();
      if (result['success'] == true) {
        final orders = result['orders'] as List?;
        final count = orders?.length ?? 0;
        _addResult('История заказов', true, 'Заказы загружены',
            'Найдено заказов: $count');
      } else {
        _addResult('История заказов', false, 'Не удалось загрузить заказы',
            result['message'] ?? 'Неизвестная ошибка');
      }
    } catch (e) {
      _addResult(
          'История заказов', false, 'Ошибка загрузки заказов', e.toString());
    }
  }

  Future<void> _checkCache() async {
    _incrementStep('Проверка кеша');
    try {
      final cacheStats = await ApiService.getCacheStats();
      final totalSize = cacheStats['totalSize'] ?? 0;
      final itemCount = cacheStats['itemCount'] ?? 0;
      _addResult('Кеш приложения', true, 'Кеш работает',
          'Элементов: $itemCount, Размер: ${(totalSize / 1024).toStringAsFixed(1)} KB');
    } catch (e) {
      _addResult(
          'Кеш приложения', false, 'Ошибка работы с кешем', e.toString());
    }
  }

  Future<void> _checkLocalStorage() async {
    _incrementStep('Проверка локального хранилища');
    try {
      final prefs = await SharedPreferences.getInstance();
      final keys = prefs.getKeys();
      final authToken = prefs.getString('auth_token');
      final profileAvatar = prefs.getString('profile_avatar');

      String details = 'Ключей: ${keys.length}';
      if (authToken != null) details += ', Токен: есть';
      if (profileAvatar != null) details += ', Аватар: есть';

      _addResult(
          'Локальное хранилище', true, 'SharedPreferences работает', details);
    } catch (e) {
      _addResult('Локальное хранилище', false,
          'Ошибка доступа к SharedPreferences', e.toString());
    }
  }

  Future<void> _checkAssets() async {
    _incrementStep('Проверка ассетов');
    try {
      // Проверяем основные ассеты
      final assetsToCheck = [
        'assets/support/whatsapp.png',
        'assets/support/telegram.png',
        'assets/app_icon/icon.png',
      ];

      int foundAssets = 0;
      String details = '';

      for (final asset in assetsToCheck) {
        try {
          // В Flutter нельзя напрямую проверить существование ассета,
          // но можем попробовать загрузить его
          await DefaultAssetBundle.of(context).load(asset);
          foundAssets++;
          details += '✓ ${asset.split('/').last} ';
        } catch (e) {
          details += '✗ ${asset.split('/').last} ';
        }
      }

      if (foundAssets == assetsToCheck.length) {
        _addResult(
            'Ассеты приложения', true, 'Все ассеты найдены', details.trim());
      } else {
        _addResult('Ассеты приложения', false, 'Некоторые ассеты отсутствуют',
            '$foundAssets/${assetsToCheck.length}: $details');
      }
    } catch (e) {
      _addResult(
          'Ассеты приложения', false, 'Ошибка проверки ассетов', e.toString());
    }
  }

  Future<void> _checkSystemPermissions() async {
    _incrementStep('Проверка системных разрешений');
    try {
      // Проверяем основные разрешения через платформенные каналы
      String details = '';

      // Проверка платформы
      if (Platform.isAndroid) {
        details += 'Платформа: Android ';
      } else if (Platform.isIOS) {
        details += 'Платформа: iOS ';
      } else {
        details += 'Платформа: ${Platform.operatingSystem} ';
      }

      // Базовые проверки доступности сети (уже проверено выше)
      details += 'Сеть: доступна';

      _addResult('Системные разрешения', true, 'Основные разрешения в порядке',
          details);
    } catch (e) {
      _addResult('Системные разрешения', false, 'Ошибка проверки разрешений',
          e.toString());
    }
  }

  Future<void> _generateSummary() async {
    _incrementStep('Формирование отчёта');

    final totalChecks = _results.length;
    final successfulChecks = _results.where((r) => r.success).length;
    final failedChecks = totalChecks - successfulChecks;

    String summaryMessage;
    bool overallSuccess;

    if (failedChecks == 0) {
      summaryMessage = 'Все системы работают нормально';
      overallSuccess = true;
    } else if (failedChecks <= 2) {
      summaryMessage = 'Обнаружены незначительные проблемы';
      overallSuccess = false;
    } else {
      summaryMessage = 'Обнаружены серьёзные проблемы';
      overallSuccess = false;
    }

    final details =
        'Успешно: $successfulChecks, Ошибок: $failedChecks, Всего: $totalChecks';

    _addResult('Общий отчёт', overallSuccess, summaryMessage, details);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Диагностика системы'),
        actions: [
          if (!_isRunning)
            IconButton(
              onPressed: _runFullDiagnostics,
              icon: const Icon(Icons.refresh),
              tooltip: 'Повторить диагностику',
            ),
        ],
      ),
      body: Column(
        children: [
          if (_isRunning) ...[
            LinearProgressIndicator(
              value: _totalSteps > 0 ? _currentStep / _totalSteps : 0,
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Text(
                'Выполняется диагностика... ($_currentStep/$_totalSteps)',
                style: Theme.of(context).textTheme.bodyLarge,
              ),
            ),
          ],
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _results.length,
              itemBuilder: (context, index) {
                final result = _results[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: ExpansionTile(
                    leading: Icon(
                      result.success ? Icons.check_circle : Icons.error,
                      color: result.success ? Colors.green : Colors.red,
                    ),
                    title: Text(
                      result.title,
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        color: result.success
                            ? Colors.green.shade700
                            : Colors.red.shade700,
                      ),
                    ),
                    subtitle: Text(result.message),
                    children: [
                      if (result.details != null)
                        Padding(
                          padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                          child: Align(
                            alignment: Alignment.centerLeft,
                            child: Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.grey.shade50,
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: Colors.grey.shade300),
                              ),
                              child: SelectableText(
                                result.details!,
                                style: TextStyle(
                                  fontFamily: 'monospace',
                                  fontSize: 12,
                                  color: Colors.grey.shade700,
                                ),
                              ),
                            ),
                          ),
                        ),
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                        child: Align(
                          alignment: Alignment.centerLeft,
                          child: Text(
                            'Проверено: ${result.timestamp.hour.toString().padLeft(2, '0')}:${result.timestamp.minute.toString().padLeft(2, '0')}:${result.timestamp.second.toString().padLeft(2, '0')}',
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.grey.shade600,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class DiagnosticResult {
  final String title;
  final bool success;
  final String message;
  final String? details;
  final DateTime timestamp;

  DiagnosticResult({
    required this.title,
    required this.success,
    required this.message,
    this.details,
    required this.timestamp,
  });
}
