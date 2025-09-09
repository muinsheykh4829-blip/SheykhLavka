import 'dart:math' as math;
import 'package:flutter/material.dart';
import '../theme.dart';

/// Современный лёгкий лоадер без сторонних зависимостей.
///
/// - Гладкая анимация дуг
/// - Адаптация под основную тему/цвет
/// - Настраиваемые размер и подпись
class ModernLoader extends StatefulWidget {
  final double size;
  final Color? color;
  final String? label;

  const ModernLoader({
    super.key,
    this.size = 44,
    this.color,
    this.label,
  });

  @override
  State<ModernLoader> createState() => _ModernLoaderState();
}

class _ModernLoaderState extends State<ModernLoader>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    )..repeat();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final color = widget.color ?? AppColors.primary;
    final size = widget.size;

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        SizedBox(
          width: size,
          height: size,
          child: AnimatedBuilder(
            animation: _ctrl,
            builder: (_, __) {
              return CustomPaint(
                painter: _ArcSpinnerPainter(
                  progress: _ctrl.value,
                  color: color,
                ),
              );
            },
          ),
        ),
        if (widget.label != null) ...[
          const SizedBox(height: 12),
          Text(
            widget.label!,
            style: TextStyle(
              fontSize: 13,
              color: Colors.grey.shade700,
              fontWeight: FontWeight.w500,
            ),
          )
        ]
      ],
    );
  }
}

class _ArcSpinnerPainter extends CustomPainter {
  final double progress; // 0..1
  final Color color;

  _ArcSpinnerPainter({required this.progress, required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = size.shortestSide / 2;
    final bgPaint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = radius * 0.18
      ..color = Colors.grey.withValues(alpha: 0.12)
      ..strokeCap = StrokeCap.round;

    // Фоновое тонкое кольцо
    canvas.drawCircle(center, radius * 0.76, bgPaint);

    // Основная быстрая дуга
    final arcPaint1 = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = radius * 0.22
      ..color = color
      ..strokeCap = StrokeCap.round;

    // Вторая дуга (тоньше и плавнее)
    final arcPaint2 = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = radius * 0.14
      ..color = color.withValues(alpha: 0.55)
      ..strokeCap = StrokeCap.round;

    final start1 = progress * 2 * math.pi;
    const sweep1 = 1.6; // ~92°
    final start2 = (1 - progress) * 2 * math.pi;
    const sweep2 = 0.9; // ~52°

    final rect = Rect.fromCircle(center: center, radius: radius * 0.74);
    final rect2 = Rect.fromCircle(center: center, radius: radius * 0.52);

    canvas.drawArc(rect, start1, sweep1, false, arcPaint1);
    canvas.drawArc(rect2, start2, sweep2, false, arcPaint2);

    // Маленькая бегущая точка с лёгким свечением
    final dotAngle = start1 + sweep1;
    final dotR = radius * 0.74;
    final dx = center.dx + dotR * math.cos(dotAngle);
    final dy = center.dy + dotR * math.sin(dotAngle);
    final dotCenter = Offset(dx, dy);

    final glow = Paint()
      ..color = color.withValues(alpha: 0.35)
      ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 6);
    canvas.drawCircle(dotCenter, radius * 0.22, glow);

    final dot = Paint()..color = color;
    canvas.drawCircle(dotCenter, radius * 0.12, dot);
  }

  @override
  bool shouldRepaint(covariant _ArcSpinnerPainter oldDelegate) {
    return oldDelegate.progress != progress || oldDelegate.color != color;
  }
}
