import 'package:flutter/material.dart';
import 'modern_loader.dart';
import '../screens/category_product_screen.dart';
import '../services/api_service.dart';
import '../models/category.dart';
import '../config/api_config.dart';

class CategoriesSheet extends StatefulWidget {
  final ScrollController controller;
  const CategoriesSheet({super.key, required this.controller});

  @override
  State<CategoriesSheet> createState() => _CategoriesSheetState();
}

class _CategoriesSheetState extends State<CategoriesSheet> {
  List<Category> _categories = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    print('🔄 Начинаем загрузку категорий...');
    try {
      final response = await ApiService.getCategories();
      print('📦 Получен ответ: $response');

      if (response['success'] == true && response['data'] != null) {
        // Безопасная проверка типа
        final data = response['data'];
        final categories = (data is List ? data : <dynamic>[])
            .map((json) => Category.fromJson(json))
            .toList();

        print('✅ Загружено категорий: ${categories.length}');
        setState(() {
          _categories = categories; // Даже если список пустой
          _isLoading = false;
        });

        if (categories.isEmpty) {
          print('ℹ️ Категории пусты - добавьте через админ панель');
        }
      } else {
        print('❌ Неверный ответ сервера');
        setState(() {
          _categories = []; // Показываем пустой список
          _isLoading = false;
        });
      }
    } catch (e) {
      print('❌ Ошибка загрузки категорий: $e');
      setState(() {
        _categories = []; // При ошибке показываем пустой список
        _isLoading = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Ошибка загрузки данных')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    double dragStartY = 0;
    double dragDelta = 0;

    if (_isLoading) {
      return Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        ),
        child: const Center(child: ModernLoader(label: 'Загрузка категорий')),
      );
    }

    // Если категорий нет, показываем сообщение
    if (_categories.isEmpty) {
      return Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          boxShadow: [
            BoxShadow(
              blurRadius: 20,
              color: Colors.black12,
              offset: Offset(0, -6),
            )
          ],
        ),
        child: const Center(
          child: Padding(
            padding: EdgeInsets.all(32.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.category_outlined,
                  size: 64,
                  color: Colors.grey,
                ),
                SizedBox(height: 16),
                Text(
                  'Категории не найдены',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    color: Colors.grey,
                  ),
                ),
                SizedBox(height: 8),
                Text(
                  'Категории будут добавлены администратором',
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ),
      );
    }

    return GestureDetector(
      onVerticalDragStart: (details) {
        dragStartY = details.globalPosition.dy;
        dragDelta = 0;
      },
      onVerticalDragUpdate: (details) {
        dragDelta = details.globalPosition.dy - dragStartY;
      },
      onVerticalDragEnd: (details) {
        if (dragDelta > 60) {
          Navigator.of(context).maybePop();
        }
      },
      behavior: HitTestBehavior.opaque,
      child: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          boxShadow: [
            BoxShadow(
              blurRadius: 20,
              color: Colors.black12,
              offset: Offset(0, -6),
            )
          ],
        ),
        child: GridView.builder(
          controller: widget.controller,
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 3,
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            childAspectRatio: 0.75,
          ),
          itemCount: _categories.length,
          itemBuilder: (context, i) {
            final category = _categories[i];
            return InkWell(
              borderRadius: BorderRadius.circular(16),
              onTap: () {
                print('Category tapped: ${category.nameRu}');

                Navigator.of(context)
                    .push(
                  MaterialPageRoute(
                    builder: (_) => CategoryProductScreen(category: category),
                  ),
                )
                    .then((result) {
                  print('Navigation completed');
                }).catchError((error) {
                  print('Navigation error: $error');
                });
              },
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Stack(
                  children: [
                    // Фон (иконка растягивается на весь размер карточки)
                    Positioned.fill(
                      child: Container(
                        color: const Color(0xFFF7F7F9),
                        child: _buildCategoryImage(category),
                      ),
                    ),
                    // Градиент сверху под текст
                    Positioned(
                      left: 0,
                      right: 0,
                      top: 0,
                      child: Container(
                        padding: const EdgeInsets.fromLTRB(8, 8, 8, 6),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [
                              Colors.black.withValues(alpha: 0.45),
                              Colors.black.withValues(alpha: 0.0),
                            ],
                          ),
                        ),
                        child: Text(
                          category.nameRu.isNotEmpty
                              ? category.nameRu
                              : category.name,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontWeight: FontWeight.w700,
                            fontSize: 13,
                            color: Colors.black,
                            height: 1.15,
                            shadows: [
                              Shadow(
                                offset: Offset(0, 1),
                                blurRadius: 2,
                                color: Colors.white,
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}

Widget _buildCategoryImage(Category category) {
  final iconPath = category.icon;
  if (iconPath == null || iconPath.isEmpty) {
    return Center(
      child: FractionallySizedBox(
        widthFactor: 0.8, // слегка уже
        child: Image.asset(
          'assets/categories/vegetables.png',
          fit: BoxFit.contain,
        ),
      ),
    );
  }
  // Если это абсолютный URL или относительный путь из uploads/storage
  final isAbsolute =
      iconPath.startsWith('http://') || iconPath.startsWith('https://');
  final isRelativeUpload =
      iconPath.startsWith('uploads/') || iconPath.startsWith('/uploads/');
  final isRelativeStorage =
      iconPath.startsWith('storage/') || iconPath.startsWith('/storage/');

  if (isAbsolute || isRelativeUpload || isRelativeStorage) {
    String url = iconPath;
    if (!isAbsolute) {
      // Удаляем ведущий слэш чтобы не получить двойной
      final cleaned =
          iconPath.startsWith('/') ? iconPath.substring(1) : iconPath;
      url = ApiConfig.fileUrl(cleaned); // используем origin без /api/v1
    }
    return Center(
      child: FractionallySizedBox(
        widthFactor: 0.8, // уменьшение ширины
        child: Image.network(
          url,
          fit: BoxFit.contain,
          errorBuilder: (c, e, s) => const Center(
            child: Icon(Icons.broken_image, color: Colors.grey, size: 42),
          ),
        ),
      ),
    );
  } else {
    return Center(
      child: FractionallySizedBox(
        widthFactor: 0.8,
        child: Image.asset(
          iconPath,
          fit: BoxFit.contain,
          errorBuilder: (c, e, s) => const Center(
            child:
                Icon(Icons.image_not_supported, color: Colors.grey, size: 42),
          ),
        ),
      ),
    );
  }
}
