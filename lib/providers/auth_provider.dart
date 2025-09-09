import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  bool _isAuthenticated = false;
  bool _isLoading = true;

  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;

  AuthProvider() {
    _checkAuthStatus();
  }

  // Проверка статуса авторизации при инициализации
  Future<void> _checkAuthStatus() async {
    try {
      _isAuthenticated = await ApiService.isAuthenticated();

      // Если пользователь авторизован, загружаем его данные с сервера
      if (_isAuthenticated) {
        await _loadUserProfileFromServer();
      }
    } catch (e) {
      _isAuthenticated = false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Вход в систему
  Future<void> login() async {
    _isAuthenticated = true;
    notifyListeners();

    // Загружаем данные профиля с сервера после успешного входа
    await _loadUserProfileFromServer();
  }

  // Загрузка данных профиля пользователя с сервера
  Future<void> _loadUserProfileFromServer() async {
    try {
      final result = await ApiService.getProfile();

      if (result['success'] == true && result['data'] != null) {
        final user = result['data']['user'];

        // Сохраняем данные локально для быстрого доступа
        final prefs = await SharedPreferences.getInstance();

        if (user['first_name'] != null) {
          await prefs.setString('profile_first_name', user['first_name']);
        }
        if (user['last_name'] != null) {
          await prefs.setString('profile_last_name', user['last_name']);
        }
        if (user['phone'] != null) {
          await prefs.setString('phone', user['phone']);
        }
        if (user['gender'] != null) {
          await prefs.setString('profile_gender', user['gender']);
        }
        if (user['avatar'] != null) {
          await prefs.setString('profile_avatar', user['avatar']);
        }
        if (user['id'] != null) {
          await prefs.setString('user_id', user['id'].toString());
        }
      }
    } catch (e) {
      // Игнорируем ошибки загрузки профиля - пользователь все равно вошел
      print('Ошибка загрузки профиля: $e');
    }
  }

  // Выход из системы
  Future<void> logout() async {
    try {
      await ApiService.removeToken();
      _isAuthenticated = false;
      notifyListeners();
    } catch (e) {
      // Игнорируем ошибки при выходе
    }
  }

  // Принудительное обновление статуса авторизации
  Future<void> refreshAuthStatus() async {
    _isLoading = true;
    notifyListeners();
    await _checkAuthStatus();
  }
}
