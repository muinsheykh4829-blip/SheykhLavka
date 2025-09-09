import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class SmsCodeScreen extends StatefulWidget {
  final String phoneNumber;
  final int userId;
  final VoidCallback onSuccess;

  const SmsCodeScreen(
      {super.key,
      required this.phoneNumber,
      required this.userId,
      required this.onSuccess});

  @override
  State<SmsCodeScreen> createState() => _SmsCodeScreenState();
}

class _SmsCodeScreenState extends State<SmsCodeScreen> {
  // void _onCodeChanged(String value) {}

  final TextEditingController _controller = TextEditingController();
  int _seconds = 60;
  Timer? _timer;
  String? _errorText;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _startTimer();
  }

  void _startTimer() {
    _timer?.cancel();
    setState(() => _seconds = 60);
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_seconds > 0) {
        setState(() => _seconds--);
      } else {
        timer.cancel();
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _onConfirm() async {
    final code = _controller.text.trim();

    if (code.length != 4) {
      setState(() {
        _errorText = 'Введите 4-значный код';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorText = null;
    });

    // Подтверждение через API
    final result = await ApiService.verifyCode(
      userId: widget.userId,
      code: code,
    );

    setState(() {
      _isLoading = false;
    });

    if (result['success']) {
      // Успешное подтверждение
      widget.onSuccess();
    } else {
      // Ошибка подтверждения
      setState(() {
        _errorText = result['message'] ?? 'Неверный код подтверждения';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const SizedBox(height: 60),
                  const Text(
                    'Введите код',
                    style: TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.w800,
                      color: Colors.black,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 18),
                  const Text(
                    'Мы отправили SMS-код на номер',
                    style: TextStyle(fontSize: 17, color: Color(0xFF7B7B7B)),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    widget.phoneNumber,
                    style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.black),
                  ),
                  const SizedBox(height: 8),
                  // Подсказка для разработки
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.green.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.green.shade200),
                    ),
                    child: Text(
                      'Код для разработки: 1234',
                      style: TextStyle(
                        color: Colors.green.shade700,
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF7F7F9),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: TextField(
                      controller: _controller,
                      keyboardType: TextInputType.number,
                      maxLength: 4,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                          fontSize: 32,
                          letterSpacing: 24,
                          color: Color(0xFFBDBDBD)),
                      decoration: InputDecoration(
                        counterText: '',
                        border: InputBorder.none,
                        hintText: '0000',
                        hintStyle: const TextStyle(
                            fontSize: 32,
                            color: Color(0xFFBDBDBD),
                            letterSpacing: 24),
                        errorText: _errorText,
                        contentPadding: const EdgeInsets.symmetric(vertical: 8),
                      ),
                      // onChanged: _onCodeChanged,
                    ),
                  ),
                  const SizedBox(height: 28),
                  SizedBox(
                    width: double.infinity,
                    height: 54,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF3CB371),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 0,
                      ),
                      onPressed: _isLoading ? null : _onConfirm,
                      child: _isLoading
                          ? const SizedBox(
                              width: 24,
                              height: 24,
                              child: CircularProgressIndicator(
                                color: Colors.white,
                                strokeWidth: 2,
                              ),
                            )
                          : const Text(
                              'Подтвердить',
                              style: TextStyle(
                                  fontSize: 20, fontWeight: FontWeight.w600),
                            ),
                    ),
                  ),
                  const SizedBox(height: 18),
                  Text(
                    _seconds > 0
                        ? 'Отправить код ещё раз через $_seconds сек.'
                        : 'Отправить код ещё раз',
                    style:
                        const TextStyle(fontSize: 14, color: Color(0xFFBDBDBD)),
                  ),
                  const SizedBox(height: 40),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
