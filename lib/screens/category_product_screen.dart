import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/product.dart';
import '../models/category.dart';
import '../models/cart_model.dart';
import '../services/api_service.dart';
import '../theme.dart';
import '../widgets/weight_calculator_dialog.dart';
import '../widgets/modern_loader.dart';

class CategoryProductScreen extends StatefulWidget {
  final Category category;

  const CategoryProductScreen({
    super.key,
    required this.category,
  });

  @override
  State<CategoryProductScreen> createState() => _CategoryProductScreenState();
}

class _CategoryProductScreenState extends State<CategoryProductScreen> {
  List<Product> _products = [];
  bool _isLoading = true;
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
    print(
        'CategoryProductScreen initState for category: ${widget.category.nameRu}');
    _loadProducts();
  }

  Future<void> _loadProducts() async {
    print(
        'Loading products for category: ${widget.category.id} - ${widget.category.nameRu}');
    // Начинаем загрузку (виджет ещё смонтирован, т.к. мы в initState или pull-to-refresh)
    if (mounted) {
      setState(() {
        _isLoading = true;
        _errorMessage = '';
      });
    }

    try {
      final response =
          await ApiService.getProducts(categoryId: widget.category.id);

      print('API Response: $response');

      if (response['success'] == true && response['data'] != null) {
        // Безопасная проверка типа
        final data = response['data'];
        final products = (data is List ? data : <dynamic>[])
            .map((json) => Product.fromJson(json))
            .where((product) => product.isActive)
            .toList();

        print('Loaded ${products.length} products');

        if (!mounted) return; // Экран уже закрыт — выходим без setState
        setState(() {
          _products = products;
          _isLoading = false;
        });
      } else {
        print('API Error: ${response['message']}');
        if (!mounted) return;
        setState(() {
          _products = [];
          _isLoading = false;
          _errorMessage = response['message'] ?? 'Не удалось загрузить товары';
        });
      }
    } catch (e) {
      print('Exception in _loadProducts: $e');
      if (!mounted) return;
      setState(() {
        _products = [];
        _isLoading = false;
        _errorMessage = 'Ошибка загрузки товаров: ${e.toString()}';
      });
    }
  }

  void _addToCart(Product product, CartModel cart) {
    if (product.isWeightProduct) {
      // Открываем диалог калькулятора веса для весовых товаров
      showDialog(
        context: context,
        builder: (context) => WeightCalculatorDialog(
          productName: product.getLocalizedName(),
          productPrice: product.currentPrice,
          unit: product.unit,
          onConfirm: (weight) {
            cart.add(product, weight: weight);
          },
        ),
      );
    } else {
      // Обычное добавление для штучных товаров
      cart.add(product);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // Белый фон вместо серого из темы
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.black),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          widget.category.name,
          style: const TextStyle(
            color: Colors.black,
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
      ),
      body: _isLoading
          ? const Center(child: ModernLoader(label: 'Загружаем товары'))
          : _errorMessage.isNotEmpty
              ? _buildErrorWidget()
              : _products.isEmpty
                  ? _buildEmptyWidget()
                  : _buildProductGrid(),
    );
  }

  Widget _buildErrorWidget() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.error_outline,
            size: 64,
            color: Colors.grey,
          ),
          const SizedBox(height: 16),
          const Text(
            'Ошибка загрузки',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _errorMessage,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 14,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: _loadProducts,
            child: const Text('Повторить'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyWidget() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.shopping_basket_outlined,
            size: 64,
            color: Colors.grey,
          ),
          const SizedBox(height: 16),
          const Text(
            'Товары не найдены',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'В категории "${widget.category.name}" пока нет товаров',
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 14,
              color: Colors.grey,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProductGrid() {
    return RefreshIndicator(
      onRefresh: _loadProducts,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: GridView.builder(
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 0.62, // Было 0.75, стало ниже — карточка выше
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
          ),
          itemCount: _products.length,
          itemBuilder: (context, index) {
            final product = _products[index];
            return _buildProductCard(product);
          },
        ),
      ),
    );
  }

  Widget _buildProductCard(Product product) {
    return Consumer<CartModel>(
      builder: (context, cart, child) {
        final cartItem = cart.items.firstWhere(
          (item) => item.product.id == product.id,
          orElse: () => CartItem(product: product, qty: 0),
        );
        final inCart = cartItem.qty > 0;
        final quantity = cartItem.qty;

        return Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade200, width: 1),
            boxShadow: const [
              BoxShadow(
                color: Color(0x14000000), // легкая тень
                blurRadius: 8,
                offset: Offset(0, 2),
              ),
              BoxShadow(
                color: Color(0x05000000),
                blurRadius: 2,
                offset: Offset(0, 1),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Изображение товара (уменьшенный flex)
              Expanded(
                flex: 3, // Было 5, стало 3
                child: Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: Colors.grey[100],
                    borderRadius: const BorderRadius.vertical(
                      top: Radius.circular(12),
                    ),
                  ),
                  child: product.images.isNotEmpty
                      ? ClipRRect(
                          borderRadius: const BorderRadius.vertical(
                            top: Radius.circular(12),
                          ),
                          child: Image.network(
                            product.images.first,
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) =>
                                _buildPlaceholder(),
                          ),
                        )
                      : _buildPlaceholder(),
                ),
              ),

              // Информация о товаре
              Expanded(
                flex: 2,
                child: Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        product.name,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      if (product.descriptionRu.isNotEmpty) ...[
                        Text(
                          product.descriptionRu,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey[600],
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),
                      ],
                      const SizedBox(height: 4),
                      const Spacer(),

                      // Кастомная кнопка/счетчик для штучного товара
                      SizedBox(
                        width: double.infinity,
                        height: 28,
                        child: !inCart
                            ? GestureDetector(
                                onTap: () => _addToCart(product, cart),
                                child: Container(
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFFDE8EC),
                                    borderRadius: BorderRadius.circular(24),
                                  ),
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 18, vertical: 0),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        product
                                            .formattedCurrentPriceWithKopecks,
                                        style: const TextStyle(
                                          fontSize: 20,
                                          fontWeight: FontWeight.w600,
                                          color: Color(0xFF222222),
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      const Icon(
                                        Icons.add,
                                        color: Color(0xFFD7263D),
                                        size: 28,
                                      ),
                                    ],
                                  ),
                                ),
                              )
                            : (product.isWeightProduct
                                ? GestureDetector(
                                    onTap: () => _addToCart(product, cart),
                                    child: Container(
                                      alignment: Alignment.center,
                                      decoration: BoxDecoration(
                                        color:
                                            AppColors.primary.withOpacity(0.1),
                                        borderRadius: BorderRadius.circular(8),
                                        border: Border.all(
                                          color: AppColors.primary,
                                          width: 1,
                                        ),
                                      ),
                                      child: Text(
                                        cartItem.displayQuantity,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          color: AppColors.primary,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ),
                                  )
                                : Container(
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFFDE8EC),
                                      borderRadius: BorderRadius.circular(24),
                                    ),
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 6, vertical: 0),
                                    child: Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        IconButton(
                                          icon: const Icon(Icons.remove,
                                              color: Color(0xFFD7263D),
                                              size: 20),
                                          padding: EdgeInsets.zero,
                                          constraints: const BoxConstraints(
                                              minWidth: 28, minHeight: 28),
                                          onPressed: () => cart.dec(product),
                                        ),
                                        Padding(
                                          padding: const EdgeInsets.symmetric(
                                              horizontal: 8),
                                          child: Text(
                                            quantity.toString(),
                                            style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 18,
                                              color: Color(0xFF222222),
                                            ),
                                          ),
                                        ),
                                        IconButton(
                                          icon: const Icon(Icons.add,
                                              color: Color(0xFFD7263D),
                                              size: 20),
                                          padding: EdgeInsets.zero,
                                          constraints: const BoxConstraints(
                                              minWidth: 28, minHeight: 28),
                                          onPressed: () => cart.inc(product),
                                        ),
                                      ],
                                    ),
                                  )),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildPlaceholder() {
    return Container(
      width: double.infinity,
      height: double.infinity,
      decoration: BoxDecoration(
        color: Colors.grey[100],
        borderRadius: const BorderRadius.vertical(
          top: Radius.circular(12),
        ),
      ),
      child: const Icon(
        Icons.image,
        size: 48,
        color: Colors.grey,
      ),
    );
  }
}
