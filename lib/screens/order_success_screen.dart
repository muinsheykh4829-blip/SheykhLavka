import 'package:flutter/material.dart';
import 'dart:async';
import 'home_screen.dart';
import 'order_history_screen.dart';

/// Переиспользуемый экран-обертка: показывает модальный успех поверх затемнённого бэкграунда
class OrderSuccessScreen extends StatefulWidget {
  const OrderSuccessScreen({super.key});

  @override
  State<OrderSuccessScreen> createState() => _OrderSuccessScreenState();
}

class _OrderSuccessScreenState extends State<OrderSuccessScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scale;
  late Animation<double> _checkStroke;
  bool _navigated = false;
  Timer? _autoTimer;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    );
    _scale = CurvedAnimation(
      parent: _controller,
      curve: const Interval(0.0, 0.45, curve: Curves.easeOutBack),
    );
    _checkStroke = CurvedAnimation(
      parent: _controller,
      curve: const Interval(0.40, 1.0, curve: Curves.easeOutQuart),
    );
    _controller.forward();
    // Авто-переход на главную через 7 секунд если пользователь не нажал кнопки
    _autoTimer = Timer(const Duration(seconds: 7), () {
      if (mounted && !_navigated) {
        _goHome();
      }
    });
  }

  @override
  void dispose() {
    _autoTimer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  void _goHome() {
    if (_navigated) return;
    _navigated = true;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const HomeScreen()),
      (route) => false,
    );
  }

  void _trackOrder() {
    if (_navigated) return;
    _navigated = true;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const HomeScreen()),
      (route) => false,
    );
    // Затем откроем историю заказов
    Future.delayed(const Duration(milliseconds: 150), () {
      if (!mounted) return;
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => const OrderHistoryScreen()),
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // Прозрачный сам экран; затемнение теперь даёт route.barrierColor
      backgroundColor: Colors.transparent,
      body: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 340),
          child: Material(
            color: Colors.white,
            elevation: 8,
            borderRadius: BorderRadius.circular(18),
            child: Padding(
              padding: const EdgeInsets.fromLTRB(18, 28, 18, 22),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  ScaleTransition(
                    scale: _scale,
                    child: _AnimatedCheck(progress: _checkStroke),
                  ),
                  const SizedBox(height: 20),
                  const Text(
                    'Заказ оформлен',
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 10),
                  const Text(
                    'Спасибо! Мы начали обработку. Вы можете отслеживать статус заказа или вернуться на главную.',
                    style: TextStyle(
                        fontSize: 14, color: Colors.black54, height: 1.4),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 26),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            side: const BorderSide(
                                color: Color(0xFF22A447), width: 1.4),
                          ),
                          onPressed: _trackOrder,
                          child: const Text(
                            'Отследить',
                            style: TextStyle(
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF22A447),
                              fontSize: 15,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF22A447),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          onPressed: _goHome,
                          child: const Text(
                            'На главную',
                            style: TextStyle(
                              fontWeight: FontWeight.w700,
                              fontSize: 15,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// Рисует круг с анимированной галочкой
class _AnimatedCheck extends StatelessWidget {
  final Animation<double> progress;
  const _AnimatedCheck({required this.progress});

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: progress,
      builder: (context, _) {
        return Container(
          width: 110,
          height: 110,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            gradient: const LinearGradient(
              colors: [Color(0xFF22A447), Color(0xFF4ADE80)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF22A447).withOpacity(0.35),
                blurRadius: 22,
                offset: const Offset(0, 10),
              )
            ],
          ),
          child: CustomPaint(
            painter: _CheckPainter(progress.value),
            child: const SizedBox.expand(),
          ),
        );
      },
    );
  }
}

class _CheckPainter extends CustomPainter {
  final double t; // 0..1
  _CheckPainter(this.t);

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white
      ..strokeWidth = 10
      ..strokeCap = StrokeCap.round
      ..style = PaintingStyle.stroke;

    // Путь галочки
    final path = Path();
    final start = Offset(size.width * 0.28, size.height * 0.53);
    final mid = Offset(size.width * 0.45, size.height * 0.70);
    final end = Offset(size.width * 0.74, size.height * 0.35);

    // Интерполяция по длине (две линии)
    if (t <= 0.5) {
      final p = t / 0.5;
      final current = Offset.lerp(start, mid, p)!;
      path.moveTo(start.dx, start.dy);
      path.lineTo(current.dx, current.dy);
    } else {
      path.moveTo(start.dx, start.dy);
      path.lineTo(mid.dx, mid.dy);
      final p = (t - 0.5) / 0.5;
      final current = Offset.lerp(mid, end, p.clamp(0, 1));
      path.lineTo(current!.dx, current.dy);
    }

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(_CheckPainter oldDelegate) => oldDelegate.t != t;
}
