import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthProvider extends ChangeNotifier {
  String? _token;
  Map<String, dynamic>? _courier;
  bool _isLoading = false;

  String? get token => _token;
  Map<String, dynamic>? get courier => _courier;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _token != null;

  void setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  Future<void> login(String token, Map<String, dynamic> courier) async {
    _token = token;
    _courier = courier;

    // Сохраняем данные в SharedPreferences
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('courier_token', token);
    await prefs.setString('courier_id', courier['id'].toString());
    await prefs.setString('courier_name', courier['name']);
    await prefs.setString('courier_login', courier['login']);
    if (courier['phone'] != null) {
      await prefs.setString('courier_phone', courier['phone']);
    }

    notifyListeners();
  }

  Future<void> logout() async {
    _token = null;
    _courier = null;

    // Очищаем SharedPreferences
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('courier_token');
    await prefs.remove('courier_id');
    await prefs.remove('courier_name');
    await prefs.remove('courier_login');
    await prefs.remove('courier_phone');

    notifyListeners();
  }

  Future<void> loadSavedCredentials() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('courier_token');

    if (token != null) {
      _token = token;
      _courier = {
        'id': int.tryParse(prefs.getString('courier_id') ?? '0') ?? 0,
        'name': prefs.getString('courier_name') ?? '',
        'login': prefs.getString('courier_login') ?? '',
        'phone': prefs.getString('courier_phone'),
      };
      notifyListeners();
    }
  }
}
