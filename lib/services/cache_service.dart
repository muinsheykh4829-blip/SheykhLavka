import 'dart:convert';
import 'dart:developer' as developer;
import 'package:shared_preferences/shared_preferences.dart';

class CacheService {
  static const String _debugEmoji = '📋';

  // Ключи для кеширования
  static const String _categoriesKey = 'cached_categories';
  static const String _productsKey = 'cached_products';
  static const String _bannersKey = 'cached_banners';
  static const String _profileKey = 'cached_profile';

  // Ключи для времени кеширования
  static const String _categoriesTimeKey = 'categories_cache_time';
  static const String _productsTimeKey = 'products_cache_time';
  static const String _bannersTimeKey = 'banners_cache_time';
  static const String _profileTimeKey = 'profile_cache_time';

  // Время жизни кеша в секундах (ТЕСТОВЫЙ РЕЖИМ - 2 секунды)
  static const int categoriesCacheDuration = 2; // 2 секунды (было 24 часа)
  static const int productsCacheDuration = 2; // 2 секунды (было 1 час)
  static const int bannersCacheDuration = 2; // 2 секунды (было 12 часов)
  static const int profileCacheDuration = 2; // 2 секунды (было 30 минут)
  static const int searchCacheDuration = 2; // 2 секунды (было 5 минут)

  static Future<SharedPreferences> _getPrefs() async {
    return await SharedPreferences.getInstance();
  }

  /// Проверяет, актуален ли кеш
  static bool _isCacheValid(int cacheTime, int duration) {
    final currentTime = DateTime.now().millisecondsSinceEpoch ~/ 1000;
    return (currentTime - cacheTime) < duration;
  }

  /// Логирование кеша
  static void _log(String message) {
    developer.log('$_debugEmoji $message', name: 'CacheService');
  }

  // КАТЕГОРИИ
  static Future<Map<String, dynamic>?> getCachedCategories() async {
    try {
      final prefs = await _getPrefs();
      final cacheTime = prefs.getInt(_categoriesTimeKey) ?? 0;

      if (_isCacheValid(cacheTime, categoriesCacheDuration)) {
        final cachedData = prefs.getString(_categoriesKey);
        if (cachedData != null) {
          _log('✅ Категории загружены из кеша (кеш 72 часа)');
          return json.decode(cachedData);
        }
      }
      _log('❌ Кеш категорий устарел или не найден');
      return null;
    } catch (e) {
      _log('⚠️ Ошибка загрузки кеша категорий: $e');
      return null;
    }
  }

  static Future<void> setCachedCategories(Map<String, dynamic> data) async {
    try {
      final prefs = await _getPrefs();
      await prefs.setString(_categoriesKey, json.encode(data));
      await prefs.setInt(
          _categoriesTimeKey, DateTime.now().millisecondsSinceEpoch ~/ 1000);
      _log('💾 Категории сохранены в кеш');
    } catch (e) {
      _log('⚠️ Ошибка сохранения категорий в кеш: $e');
    }
  }

  // ПРОДУКТЫ
  static Future<Map<String, dynamic>?> getCachedProducts(
      {String? categoryId, String? search}) async {
    try {
      final prefs = await _getPrefs();
      final key = categoryId != null
          ? '${_productsKey}_cat_$categoryId'
          : search != null
              ? '${_productsKey}_search_${search.hashCode}'
              : _productsKey;
      final timeKey = categoryId != null
          ? '${_productsTimeKey}_cat_$categoryId'
          : search != null
              ? '${_productsTimeKey}_search_${search.hashCode}'
              : _productsTimeKey;

      final cacheTime = prefs.getInt(timeKey) ?? 0;
      final duration =
          search != null ? searchCacheDuration : productsCacheDuration;

      if (_isCacheValid(cacheTime, duration)) {
        final cachedData = prefs.getString(key);
        if (cachedData != null) {
          final cacheType = search != null
              ? 'поиска'
              : categoryId != null
                  ? 'категории'
                  : 'товаров';
          final cacheDurationStr = search != null ? '5 минут' : '12 часов';
          _log('✅ Товары $cacheType загружены из кеша (кеш $cacheDurationStr)');
          return json.decode(cachedData);
        }
      }
      _log('❌ Кеш товаров устарел или не найден');
      return null;
    } catch (e) {
      _log('⚠️ Ошибка загрузки кеша товаров: $e');
      return null;
    }
  }

  static Future<void> setCachedProducts(Map<String, dynamic> data,
      {String? categoryId, String? search}) async {
    try {
      final prefs = await _getPrefs();
      final key = categoryId != null
          ? '${_productsKey}_cat_$categoryId'
          : search != null
              ? '${_productsKey}_search_${search.hashCode}'
              : _productsKey;
      final timeKey = categoryId != null
          ? '${_productsTimeKey}_cat_$categoryId'
          : search != null
              ? '${_productsTimeKey}_search_${search.hashCode}'
              : _productsTimeKey;

      await prefs.setString(key, json.encode(data));
      await prefs.setInt(
          timeKey, DateTime.now().millisecondsSinceEpoch ~/ 1000);

      final cacheType = search != null
          ? 'поиска'
          : categoryId != null
              ? 'категории'
              : 'товаров';
      _log('💾 Товары $cacheType сохранены в кеш');
    } catch (e) {
      _log('⚠️ Ошибка сохранения товаров в кеш: $e');
    }
  }

  // БАННЕРЫ
  static Future<Map<String, dynamic>?> getCachedBanners() async {
    try {
      final prefs = await _getPrefs();
      final cacheTime = prefs.getInt(_bannersTimeKey) ?? 0;

      if (_isCacheValid(cacheTime, bannersCacheDuration)) {
        final cachedData = prefs.getString(_bannersKey);
        if (cachedData != null) {
          _log('✅ Баннеры загружены из кеша (кеш 4 дня)');
          return json.decode(cachedData);
        }
      }
      _log('❌ Кеш баннеров устарел или не найден');
      return null;
    } catch (e) {
      _log('⚠️ Ошибка загрузки кеша баннеров: $e');
      return null;
    }
  }

  static Future<void> setCachedBanners(Map<String, dynamic> data) async {
    try {
      final prefs = await _getPrefs();
      await prefs.setString(_bannersKey, json.encode(data));
      await prefs.setInt(
          _bannersTimeKey, DateTime.now().millisecondsSinceEpoch ~/ 1000);
      _log('💾 Баннеры сохранены в кеш');
    } catch (e) {
      _log('⚠️ Ошибка сохранения баннеров в кеш: $e');
    }
  }

  // ПРОФИЛЬ
  static Future<Map<String, dynamic>?> getCachedProfile() async {
    try {
      final prefs = await _getPrefs();
      final cacheTime = prefs.getInt(_profileTimeKey) ?? 0;

      if (_isCacheValid(cacheTime, profileCacheDuration)) {
        final cachedData = prefs.getString(_profileKey);
        if (cachedData != null) {
          _log('✅ Профиль загружен из кеша (кеш 30 минут)');
          return json.decode(cachedData);
        }
      }
      _log('❌ Кеш профиля устарел или не найден');
      return null;
    } catch (e) {
      _log('⚠️ Ошибка загрузки кеша профиля: $e');
      return null;
    }
  }

  static Future<void> setCachedProfile(Map<String, dynamic> data) async {
    try {
      final prefs = await _getPrefs();
      await prefs.setString(_profileKey, json.encode(data));
      await prefs.setInt(
          _profileTimeKey, DateTime.now().millisecondsSinceEpoch ~/ 1000);
      _log('💾 Профиль сохранен в кеш');
    } catch (e) {
      _log('⚠️ Ошибка сохранения профиля в кеш: $e');
    }
  }

  // ОЧИСТКА КЕША
  static Future<void> clearCache() async {
    try {
      final prefs = await _getPrefs();
      await prefs.remove(_categoriesKey);
      await prefs.remove(_categoriesTimeKey);
      await prefs.remove(_productsKey);
      await prefs.remove(_productsTimeKey);
      await prefs.remove(_bannersKey);
      await prefs.remove(_bannersTimeKey);
      await prefs.remove(_profileKey);
      await prefs.remove(_profileTimeKey);

      // Очистка кеша поиска
      final keys = prefs.getKeys();
      for (final key in keys) {
        if (key.startsWith(_productsKey) || key.startsWith(_productsTimeKey)) {
          await prefs.remove(key);
        }
      }

      _log('🗑️ Весь кеш очищен');
    } catch (e) {
      _log('⚠️ Ошибка очистки кеша: $e');
    }
  }

  static Future<void> clearProductsCache() async {
    try {
      final prefs = await _getPrefs();
      final keys = prefs.getKeys();
      for (final key in keys) {
        if (key.startsWith(_productsKey) || key.startsWith(_productsTimeKey)) {
          await prefs.remove(key);
        }
      }
      _log('🗑️ Кеш товаров очищен');
    } catch (e) {
      _log('⚠️ Ошибка очистки кеша товаров: $e');
    }
  }

  static Future<void> clearProfileCache() async {
    try {
      final prefs = await _getPrefs();
      await prefs.remove(_profileKey);
      await prefs.remove(_profileTimeKey);
      _log('🗑️ Кеш профиля очищен');
    } catch (e) {
      _log('⚠️ Ошибка очистки кеша профиля: $e');
    }
  }

  // СТАТИСТИКА КЕША
  static Future<Map<String, dynamic>> getCacheStats() async {
    try {
      final prefs = await _getPrefs();
      final currentTime = DateTime.now().millisecondsSinceEpoch ~/ 1000;

      return {
        'categories': {
          'cached': prefs.containsKey(_categoriesKey),
          'valid': _isCacheValid(
              prefs.getInt(_categoriesTimeKey) ?? 0, categoriesCacheDuration),
          'expires_in': categoriesCacheDuration -
              (currentTime - (prefs.getInt(_categoriesTimeKey) ?? 0)),
        },
        'products': {
          'cached': prefs.containsKey(_productsKey),
          'valid': _isCacheValid(
              prefs.getInt(_productsTimeKey) ?? 0, productsCacheDuration),
          'expires_in': productsCacheDuration -
              (currentTime - (prefs.getInt(_productsTimeKey) ?? 0)),
        },
        'banners': {
          'cached': prefs.containsKey(_bannersKey),
          'valid': _isCacheValid(
              prefs.getInt(_bannersTimeKey) ?? 0, bannersCacheDuration),
          'expires_in': bannersCacheDuration -
              (currentTime - (prefs.getInt(_bannersTimeKey) ?? 0)),
        },
        'profile': {
          'cached': prefs.containsKey(_profileKey),
          'valid': _isCacheValid(
              prefs.getInt(_profileTimeKey) ?? 0, profileCacheDuration),
          'expires_in': profileCacheDuration -
              (currentTime - (prefs.getInt(_profileTimeKey) ?? 0)),
        },
      };
    } catch (e) {
      _log('⚠️ Ошибка получения статистики кеша: $e');
      return {};
    }
  }
}
