import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  bool _isLoggedIn = false;
  String _pickerName = '';
  String _pickerLogin = '';

  bool get isLoggedIn => _isLoggedIn;
  String get pickerName => _pickerName;
  String get pickerLogin => _pickerLogin;

  void setLoggedIn(bool value, {String? pickerName, String? pickerLogin}) {
    _isLoggedIn = value;
    if (pickerName != null) _pickerName = pickerName;
    if (pickerLogin != null) _pickerLogin = pickerLogin;
    notifyListeners();
  }

  Future<void> logout() async {
    _isLoggedIn = false;
    _pickerName = '';
    _pickerLogin = '';

    // Очищаем сохраненные данные
    await ApiService.logout();

    notifyListeners();
  }
}
