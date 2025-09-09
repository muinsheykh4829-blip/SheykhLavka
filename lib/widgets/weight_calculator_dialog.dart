import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

class WeightCalculatorDialog extends StatefulWidget {
  final String productName;
  final double productPrice;
  final String unit;
  final Function(double weight) onConfirm;

  const WeightCalculatorDialog({
    super.key,
    required this.productName,
    required this.productPrice,
    required this.unit,
    required this.onConfirm,
  });

  @override
  State<WeightCalculatorDialog> createState() => _WeightCalculatorDialogState();
}

class _WeightCalculatorDialogState extends State<WeightCalculatorDialog> {
  final _controller = TextEditingController();
  bool _isKg = true; // true = кг, false = граммы
  double _weight = 0.0;
  double _totalPrice = 0.0;

  @override
  void initState() {
    super.initState();
    _controller.text = '1.0';
    _calculateWeight();
  }

  void _calculateWeight() {
    final inputValue = double.tryParse(_controller.text) ?? 0.0;
    if (inputValue <= 0) {
      setState(() {
        _weight = 0.0;
        _totalPrice = 0.0;
      });
      return;
    }

    setState(() {
      // Конвертируем в килограммы для расчета
      if (_isKg) {
        _weight = inputValue; // уже в кг
      } else {
        _weight = inputValue / 1000; // граммы в кг
      }
      _totalPrice = _weight * widget.productPrice;
    });
  }

  void _toggleUnit() {
    final currentValue = double.tryParse(_controller.text) ?? 0.0;
    if (currentValue > 0) {
      if (_isKg) {
        // Переключаемся с кг на граммы
        _controller.text = (currentValue * 1000).toStringAsFixed(0);
      } else {
        // Переключаемся с граммов на кг
        _controller.text = (currentValue / 1000).toStringAsFixed(3);
      }
    }
    setState(() {
      _isKg = !_isKg;
    });
    _calculateWeight();
  }

  String _getDisplayWeight() {
    if (_weight == 0) return '0 кг';

    if (_weight >= 1) {
      return '${_weight.toStringAsFixed(3)} кг';
    } else {
      return '${(_weight * 1000).toStringAsFixed(0)} г';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Заголовок
            const Text(
              'Укажите вес',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),

            // Название товара
            Text(
              widget.productName,
              style: const TextStyle(
                fontSize: 16,
                color: Colors.black54,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),

            // Поле ввода с переключателем единиц
            Row(
              children: [
                Expanded(
                  flex: 3,
                  child: TextField(
                    controller: _controller,
                    keyboardType:
                        const TextInputType.numberWithOptions(decimal: true),
                    inputFormatters: [
                      FilteringTextInputFormatter.allow(RegExp(r'^\d*\.?\d*')),
                    ],
                    decoration: InputDecoration(
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 12,
                      ),
                      hintText: _isKg ? '1.0' : '1000',
                    ),
                    onChanged: (value) => _calculateWeight(),
                  ),
                ),
                const SizedBox(width: 12),

                // Переключатель единиц
                Expanded(
                  flex: 1,
                  child: GestureDetector(
                    onTap: _toggleUnit,
                    child: Container(
                      height: 48,
                      decoration: BoxDecoration(
                        color: const Color(0xFF3CB371),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Center(
                        child: Text(
                          _isKg ? 'КГ' : 'Г',
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),

            // Информация о расчете
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFFF7F7F9),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Вес:'),
                      Text(
                        _getDisplayWeight(),
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Цена за кг:'),
                      Text('${(widget.productPrice).toStringAsFixed(2)} с'),
                    ],
                  ),
                  const Divider(),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Итого:',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        '${(_totalPrice).toStringAsFixed(2)} с',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF3CB371),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Кнопки действий
            Row(
              children: [
                Expanded(
                  child: TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text(
                      'Отмена',
                      style: TextStyle(color: Colors.black54),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  flex: 2,
                  child: ElevatedButton(
                    onPressed: _weight > 0
                        ? () {
                            widget.onConfirm(_weight);
                            Navigator.of(context).pop();
                          }
                        : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF3CB371),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    child: const Text(
                      'Добавить в корзину',
                      style: TextStyle(fontWeight: FontWeight.w600),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }
}
