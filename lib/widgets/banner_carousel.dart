import 'package:flutter/material.dart';
import 'modern_loader.dart';
import 'package:carousel_slider/carousel_slider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/banner.dart' as BannerModel;
import '../services/api_service.dart';
import '../config/api_config.dart';

class BannerCarousel extends StatefulWidget {
  const BannerCarousel({super.key});

  @override
  State<BannerCarousel> createState() => _BannerCarouselState();
}

class _BannerCarouselState extends State<BannerCarousel> {
  List<BannerModel.Banner> banners = [];
  bool isLoading = true;
  int currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadBanners();
  }

  Future<void> _loadBanners() async {
    print('🎠 Начинаем загрузку баннеров...');
    setState(() {
      isLoading = true;
    });

    try {
      final response = await ApiService.getBanners();
      print('🎠 Получен ответ: $response');

      if (response['success'] == true && response['data'] != null) {
        final List<dynamic> bannersData = response['data'];
        print('🎠 Данные баннеров: $bannersData');
        print('🎠 Количество баннеров: ${bannersData.length}');

        setState(() {
          banners = bannersData
              .map((banner) => BannerModel.Banner.fromJson(banner))
              .where((banner) => banner.isActive)
              .toList();
          banners.sort((a, b) => a.sortOrder.compareTo(b.sortOrder));
        });

        print('🎠 Активных баннеров после фильтрации: ${banners.length}');
      } else {
        print('🎠 ❌ Неуспешный ответ или нет данных');
      }
    } catch (e) {
      print('🎠 ❌ Ошибка загрузки баннеров: $e');
      debugPrint('Error loading banners: $e');
    } finally {
      setState(() {
        isLoading = false;
      });
      print('🎠 Загрузка завершена. isLoading: $isLoading');
    }
  }

  String _getImageUrl(String imagePath) {
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }
    // Баннеры находятся в папке uploads, а не storage
    if (imagePath.startsWith('uploads/')) {
      return '${ApiConfig.currentUrl}/$imagePath';
    }
    return '${ApiConfig.currentUrl}/storage/$imagePath';
  }

  @override
  Widget build(BuildContext context) {
    final screenHeight = MediaQuery.of(context).size.height;
    final bannerHeight = screenHeight * 0.65; // 65% высоты экрана

    if (isLoading) {
      return Container(
        height: bannerHeight,
        margin: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          color: Colors.grey[100],
        ),
        child: const Center(
          child: const ModernLoader(),
        ),
      );
    }

    if (banners.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      children: [
        CarouselSlider.builder(
          itemCount: banners.length,
          itemBuilder: (context, index, realIndex) {
            final banner = banners[index];

            return GestureDetector(
              onTap: null,
              child: Container(
                margin: const EdgeInsets.symmetric(
                    horizontal: 0), // Убираем горизонтальные отступы
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(
                      0), // Убираем скругление для полноэкранного эффекта
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.1),
                      blurRadius: 6,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(0), // Убираем скругление
                  child: Stack(
                    children: [
                      // Изображение баннера
                      CachedNetworkImage(
                        imageUrl: _getImageUrl(banner.image),
                        width: double.infinity,
                        height: double.infinity,
                        fit: BoxFit.cover,
                        placeholder: (context, url) => Container(
                          color: Colors.grey[200],
                          child: const Center(
                            child: const ModernLoader(size: 28),
                          ),
                        ),
                        errorWidget: (context, url, error) => Container(
                          color: Colors.grey[200],
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(
                                Icons.broken_image,
                                size: 50,
                                color: Colors.grey,
                              ),
                              const SizedBox(height: 8),
                              Text(
                                banner.getLocalizedTitle('ru'),
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w500,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ],
                          ),
                        ),
                      ),

                      // Индикатор ссылки
                      if (banner.hasLink)
                        const Positioned(
                          top: 20,
                          right: 20,
                          child: Icon(
                            Icons.open_in_new,
                            color: Colors.white,
                            size: 28, // Увеличили размер иконки
                            shadows: [
                              Shadow(
                                offset: Offset(2, 2),
                                blurRadius: 4,
                                color: Colors.black54,
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            );
          },
          options: CarouselOptions(
            height: bannerHeight,
            aspectRatio: 16 / 9,
            viewportFraction: 1.0, // Полная ширина для большего баннера
            initialPage: 0,
            enableInfiniteScroll: banners.length > 1,
            reverse: false,
            autoPlay: banners.length > 1,
            autoPlayInterval: const Duration(seconds: 5),
            autoPlayAnimationDuration: const Duration(milliseconds: 800),
            autoPlayCurve: Curves.fastOutSlowIn,
            enlargeCenterPage:
                false, // Убираем увеличение для полноэкранного режима
            enlargeFactor: 0.0,
            onPageChanged: (index, reason) {
              setState(() {
                currentIndex = index;
              });
            },
            scrollDirection: Axis.horizontal,
          ),
        ),

        // Индикаторы точек
        if (banners.length > 1) ...[
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: banners.asMap().entries.map((entry) {
              return Container(
                width: 12, // Увеличили размер
                height: 12,
                margin: const EdgeInsets.symmetric(horizontal: 6),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: currentIndex == entry.key
                      ? Theme.of(context).primaryColor
                      : Colors.grey[300],
                  border: Border.all(
                    color: Colors.white,
                    width: 2,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.2),
                      blurRadius: 4,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        ],
      ],
    );
  }
}
