import 'dart:io' show Platform;

class ApiConfig {
  // Базовый URL вашего Laravel API
  // Для эмулятора Android используйте 10.0.2.2
  // Для физического устройства используйте IP адрес компьютера
  static const String androidEmulatorUrl = 'http://10.0.2.2:8000/api/v1';
  static const String desktopUrl = 'http://127.0.0.1:8000/api/v1';
  static const String alternativeUrl =
      'http://192.168.100.87:8000/api/v1'; // Авто-подстановка локального IP для физического устройства

  // URL для продакшена (замените на ваш домен)
  static const String productionUrl = 'https://yourdomain.com/api/v1';

  // Текущий URL (переключается в зависимости от режима)
  static String get currentUrl {
    const bool isProduction = bool.fromEnvironment('dart.vm.product');

    if (isProduction) {
      // Если продакшен URL ещё не настроен (заглушка), используем локальный IP для теста
      if (productionUrl.contains('yourdomain.com')) {
        return alternativeUrl.isNotEmpty ? alternativeUrl : androidEmulatorUrl;
      }
      return productionUrl;
    }

    // Определяем URL в зависимости от платформы
    String url;
    try {
      if (Platform.isAndroid) {
        // Если вы запускаете на физическом устройстве — замените alternativeUrl на ваш IP.
        url = alternativeUrl.isNotEmpty ? alternativeUrl : androidEmulatorUrl;
      } else {
        url = desktopUrl; // Для Windows, macOS, Linux, Web
      }
    } catch (e) {
      // Если Platform недоступен (например, Web) – fallback
      url = desktopUrl;
    }

    print(
        '🔧 API URL: $url (Production: $isProduction, Platform: ${_getPlatformName()})');
    return url;
  }

  // Базовый origin без /api/v1 для статических файлов (uploads, storage, etc.)
  static String get baseOrigin {
    final uri = Uri.parse(currentUrl);
    final origin =
        '${uri.scheme}://${uri.host}${uri.hasPort ? ':${uri.port}' : ''}';
    return origin;
  }

  // Формируем абсолютный URL для статического файла
  static String fileUrl(String relativePath) {
    if (relativePath.startsWith('http://') ||
        relativePath.startsWith('https://')) {
      return relativePath; // Уже абсолютный
    }
    final cleaned =
        relativePath.startsWith('/') ? relativePath.substring(1) : relativePath;
    return '$baseOrigin/$cleaned';
  }

  static String _getPlatformName() {
    try {
      if (Platform.isAndroid) return 'Android';
      if (Platform.isIOS) return 'iOS';
      if (Platform.isWindows) return 'Windows';
      if (Platform.isMacOS) return 'macOS';
      if (Platform.isLinux) return 'Linux';
      return 'Unknown';
    } catch (e) {
      return 'Web';
    }
  }

  // Эндпоинты
  static const String loginEndpoint = '/auth/login';
  static const String registerEndpoint = '/auth/register';
  static const String ordersEndpoint = '/orders';
  static const String categoriesEndpoint = '/categories';
  static const String productsEndpoint = '/products';
  static const String cartEndpoint = '/cart';

  // Заголовки по умолчанию
  static Map<String, String> get defaultHeaders => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };

  // Заголовки с авторизацией
  static Map<String, String> authHeaders(String token) => {
        ...defaultHeaders,
        'Authorization': 'Bearer $token',
      };

  // Таймауты
  static const Duration connectTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
}
