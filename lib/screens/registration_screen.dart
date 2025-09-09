import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import 'sms_code_screen.dart';
import 'home_screen.dart';

class RegistrationScreen extends StatefulWidget {
  const RegistrationScreen({super.key});

  @override
  State<RegistrationScreen> createState() => _RegistrationScreenState();
}

class _RegistrationScreenState extends State<RegistrationScreen> {
  final TextEditingController _phoneController = TextEditingController();
  String? _errorText;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    // Контроллер остается пустым, префикс +992 отображается отдельно
  }

  Future<void> _onLogin() async {
    // Валидация - получаем только цифры из поля ввода
    final rawPhoneDigits = _phoneController.text.replaceAll(RegExp(r'\D'), '');

    if (rawPhoneDigits.length != 9) {
      // Должно быть ровно 9 цифр после +992
      setState(() {
        _errorText = 'Введите корректный номер телефона';
      });
      return;
    }

    // Формируем полный номер с префиксом +992
    final fullPhone = '+992$rawPhoneDigits';

    setState(() {
      _isLoading = true;
      _errorText = null;
    });

    try {
      // Отправляем SMS для входа
      final result = await ApiService.sendLoginSms(phone: fullPhone);

      setState(() {
        _isLoading = false;
      });

      if (result['success']) {
        // SMS отправлен - переходим к подтверждению
        final userId = result['data']['user_id'];
        if (!mounted) return;

        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => SmsCodeScreen(
              phoneNumber: fullPhone,
              userId: userId,
              onSuccess: () {
                // Уведомляем AuthProvider о успешном входе
                Provider.of<AuthProvider>(context, listen: false).login();

                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (_) => const HomeScreen()),
                  (route) => false,
                );
              },
            ),
          ),
        );
      } else {
        // Ошибка отправки SMS
        setState(() {
          _errorText = result['message'] ?? 'Ошибка отправки SMS';
        });
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
        _errorText = 'Ошибка подключения к серверу';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 40.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const Spacer(flex: 2),

              // Заголовок
              const Text(
                'Добро пожаловать',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Colors.black,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 12),

              // Подзаголовок
              const Text(
                'Войдите в приложение, используя номер\nтелефона Таджикистана',
                style: TextStyle(
                  fontSize: 16,
                  color: Color(0xFF9E9E9E),
                  height: 1.5,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 40),

              // Поле ввода номера телефона
              Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: _errorText != null
                        ? Colors.red
                        : const Color(0xFFE0E0E0),
                    width: 1,
                  ),
                ),
                child: Row(
                  children: [
                    // Префикс +992
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 16),
                      child: const Text(
                        '+992',
                        style: TextStyle(
                          fontSize: 16,
                          color: Colors.black,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),

                    // Разделитель
                    Container(
                      width: 1,
                      height: 30,
                      color: const Color(0xFFE0E0E0),
                    ),

                    // Поле ввода номера
                    Expanded(
                      child: TextField(
                        controller: _phoneController,
                        keyboardType: TextInputType.number,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                          LengthLimitingTextInputFormatter(
                              9), // 9 цифр после +992
                          _PhoneNumberFormatter(),
                        ],
                        style: const TextStyle(
                          fontSize: 16,
                          letterSpacing: 1.0,
                        ),
                        decoration: const InputDecoration(
                          hintText: 'XX XXX XX XX',
                          hintStyle: TextStyle(
                            color: Color(0xFFBDBDBD),
                            letterSpacing: 1.0,
                          ),
                          border: InputBorder.none,
                          contentPadding: EdgeInsets.symmetric(
                              horizontal: 16, vertical: 16),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              // Показываем ошибку если есть
              if (_errorText != null) ...[
                const SizedBox(height: 12),
                Text(
                  _errorText!,
                  style: const TextStyle(
                    color: Colors.red,
                    fontSize: 14,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],

              const Spacer(flex: 3),

              // Кнопка "Получить код"
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _onLogin,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4CAF50),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 0,
                  ),
                  child: _isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : const Text(
                          'Получить код',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                ),
              ),

              const SizedBox(height: 20),

              // Текст согласия
              RichText(
                textAlign: TextAlign.center,
                text: const TextSpan(
                  style: TextStyle(
                    fontSize: 12,
                    color: Color(0xFF9E9E9E),
                    height: 1.4,
                  ),
                  children: [
                    TextSpan(
                        text: 'Нажимая "Получить код", вы соглашаетесь с\n'),
                    TextSpan(
                      text: 'Условиями использования',
                      style: TextStyle(
                        color: Color(0xFF4CAF50),
                        decoration: TextDecoration.underline,
                      ),
                    ),
                    TextSpan(text: ' и '),
                    TextSpan(
                      text: 'Политикой конфиденциальности',
                      style: TextStyle(
                        color: Color(0xFF4CAF50),
                        decoration: TextDecoration.underline,
                      ),
                    ),
                  ],
                ),
              ),

              const Spacer(),
            ],
          ),
        ),
      ),
    );
  }
}

// Форматтер для номера телефона в формате XX XXX XX XX
class _PhoneNumberFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    String digits = newValue.text.replaceAll(RegExp(r'\D'), '');

    if (digits.length > 9) {
      digits = digits.substring(0, 9);
    }

    String formatted = '';
    for (int i = 0; i < digits.length; i++) {
      if (i == 2 || i == 5 || i == 7) {
        formatted += ' ';
      }
      formatted += digits[i];
    }

    return TextEditingValue(
      text: formatted,
      selection: TextSelection.collapsed(offset: formatted.length),
    );
  }
}
