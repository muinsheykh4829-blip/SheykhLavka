import 'package:flutter/material.dart';
import '../models/product.dart';
import 'weight_calculator_dialog.dart';

typedef OnChooseProduct = void Function(Product product, {double? weight});

class SearchPill extends StatelessWidget {
  final VoidCallback onTap;
  const SearchPill({super.key, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(28),
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.9),
          borderRadius: BorderRadius.circular(28),
          boxShadow: const [
            BoxShadow(
              blurRadius: 8,
              color: Colors.black12,
              offset: Offset(0, 2),
            )
          ],
        ),
        child: const Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.search, color: Colors.black54),
            SizedBox(width: 8),
            Text('Поиск', style: TextStyle(color: Colors.black87)),
          ],
        ),
      ),
    );
  }
}

class ProductSearchSheet extends StatefulWidget {
  final List<Product> products;
  final OnChooseProduct onChoose;
  const ProductSearchSheet(
      {super.key, required this.products, required this.onChoose});

  @override
  State<ProductSearchSheet> createState() => _ProductSearchSheetState();
}

class _ProductSearchSheetState extends State<ProductSearchSheet> {
  final Map<int, int> _localQty = {};

  String q = '';

  void _addToCart(Product product) {
    if (product.isWeightProduct) {
      // Открываем диалог калькулятора веса для весовых товаров
      showDialog(
        context: context,
        builder: (context) => WeightCalculatorDialog(
          productName: product.getLocalizedName(),
          productPrice: product.currentPrice,
          unit: product.unit,
          onConfirm: (weight) {
            widget.onChoose(product, weight: weight);
            Navigator.of(context).pop(); // Закрываем диалог поиска
          },
        ),
      );
    } else {
      // Обычное добавление для штучных товаров
      widget.onChoose(product);
    }
  }

  @override
  Widget build(BuildContext context) {
    final filtered = widget.products
        .where((p) => (p.nameRu + p.descriptionRu)
            .toLowerCase()
            .contains(q.toLowerCase()))
        .toList();
    return SafeArea(
      child: Container(
        color: Colors.white,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Поиск товара',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
              const SizedBox(height: 12),
              TextField(
                autofocus: true,
                decoration: const InputDecoration(
                  hintText: 'Введите название...',
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.all(Radius.circular(12))),
                ),
                onChanged: (v) => setState(() => q = v),
              ),
              const SizedBox(height: 12),
              Flexible(
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: filtered.length,
                  separatorBuilder: (_, __) => const Divider(height: 1),
                  itemBuilder: (context, i) {
                    final p = filtered[i];
                    final qty = _localQty[p.id] ?? 0;
                    Widget trailingWidget;
                    if (qty == 0) {
                      trailingWidget = IconButton(
                        icon: const Icon(Icons.add_circle_outline),
                        onPressed: () {
                          setState(() {
                            _localQty[p.id] = 1;
                          });
                          _addToCart(p);
                        },
                      );
                    } else {
                      trailingWidget = Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          IconButton(
                            icon: const Icon(Icons.remove_circle_outline),
                            onPressed: () {
                              setState(() {
                                if ((_localQty[p.id] ?? 1) > 1) {
                                  _localQty[p.id] = (_localQty[p.id] ?? 1) - 1;
                                } else {
                                  _localQty.remove(p.id);
                                }
                              });
                            },
                          ),
                          Text('$qty',
                              style:
                                  const TextStyle(fontWeight: FontWeight.w700)),
                          IconButton(
                            icon: const Icon(Icons.add_circle_outline),
                            onPressed: () {
                              setState(() {
                                _localQty[p.id] = (qty + 1);
                              });
                              _addToCart(p);
                            },
                          ),
                        ],
                      );
                    }
                    return ListTile(
                      leading: ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: p.images.isNotEmpty
                            ? Image.network(
                                p.images.first,
                                width: 48,
                                height: 48,
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) =>
                                    Container(
                                  width: 48,
                                  height: 48,
                                  color: Colors.grey[200],
                                  child: const Icon(Icons.image_not_supported),
                                ),
                              )
                            : Container(
                                width: 48,
                                height: 48,
                                color: Colors.grey[200],
                                child: const Icon(Icons.image_not_supported),
                              ),
                      ),
                      title: Text(p.nameRu),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (p.descriptionRu.isNotEmpty) Text(p.descriptionRu),
                          Text(
                            p.formattedCurrentPriceWithKopecks,
                            style: const TextStyle(
                              color: Color(0xFF22A447),
                              fontWeight: FontWeight.bold,
                              fontSize: 15,
                            ),
                          ),
                        ],
                      ),
                      trailing: trailingWidget,
                      onTap: qty == 0
                          ? () {
                              setState(() {
                                _localQty[p.id] = 1;
                              });
                              widget.onChoose(p);
                            }
                          : null,
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
