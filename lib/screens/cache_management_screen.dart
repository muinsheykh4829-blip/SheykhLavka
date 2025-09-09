import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../theme.dart';

class CacheManagementScreen extends StatefulWidget {
  const CacheManagementScreen({super.key});

  @override
  State<CacheManagementScreen> createState() => _CacheManagementScreenState();
}

class _CacheManagementScreenState extends State<CacheManagementScreen> {
  Map<String, dynamic> _cacheStats = {};
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadCacheStats();
  }

  Future<void> _loadCacheStats() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final stats = await ApiService.getCacheStats();
      setState(() {
        _cacheStats = stats;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  String _formatTimeRemaining(int seconds) {
    if (seconds <= 0) return 'Устарел';

    final hours = seconds ~/ 3600;
    final minutes = (seconds % 3600) ~/ 60;
    final remainingSeconds = seconds % 60;

    if (hours > 0) {
      return '$hoursч $minutesм';
    } else if (minutes > 0) {
      return '$minutesм $remainingSecondsс';
    } else {
      return '$remainingSecondsс';
    }
  }

  Widget _buildCacheItem(
      String title, String subtitle, Map<String, dynamic> data) {
    final bool cached = data['cached'] ?? false;
    final bool valid = data['valid'] ?? false;
    final int expiresIn = data['expires_in'] ?? 0;

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor:
              valid ? Colors.green : (cached ? Colors.orange : Colors.grey),
          child: Icon(
            cached ? (valid ? Icons.cached : Icons.schedule) : Icons.storage,
            color: Colors.white,
            size: 20,
          ),
        ),
        title: Text(title),
        subtitle: Text(subtitle),
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(
              cached ? (valid ? 'Актуальный' : 'Устарел') : 'Не кеширован',
              style: TextStyle(
                color: valid
                    ? Colors.green
                    : (cached ? Colors.orange : Colors.grey),
                fontWeight: FontWeight.w500,
              ),
            ),
            if (cached && valid)
              Text(
                'Осталось: ${_formatTimeRemaining(expiresIn)}',
                style: const TextStyle(
                  fontSize: 12,
                  color: Colors.grey,
                ),
              ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Управление кешем'),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadCacheStats,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Заголовок
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  decoration: const BoxDecoration(
                    color: AppColors.background,
                    border: Border(
                        bottom: BorderSide(color: Colors.grey, width: 0.5)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        '📋 Статус кеширования данных',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Кеширование ускоряет загрузку приложения',
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),

                // Список элементов кеша
                Expanded(
                  child: ListView(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    children: [
                      if (_cacheStats.containsKey('categories'))
                        _buildCacheItem(
                          'Категории товаров',
                          'Кеш 72 часа - меняются редко',
                          _cacheStats['categories'],
                        ),
                      if (_cacheStats.containsKey('products'))
                        _buildCacheItem(
                          'Товары',
                          'Кеш 12 часов - часто запрашиваются',
                          _cacheStats['products'],
                        ),
                      if (_cacheStats.containsKey('banners'))
                        _buildCacheItem(
                          'Баннеры',
                          'Кеш 4 дня - очень редко меняются',
                          _cacheStats['banners'],
                        ),
                      if (_cacheStats.containsKey('profile'))
                        _buildCacheItem(
                          'Профиль пользователя',
                          'Кеш 30 минут',
                          _cacheStats['profile'],
                        ),

                      // Информация о данных, которые НЕ кешируются
                      const SizedBox(height: 20),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 16),
                        child: Text(
                          'Всегда актуальные с сервера:',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            color: Colors.grey,
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),

                      _buildInfoItem(
                        '🛒 Корзина',
                        'Всегда актуальная с сервера',
                        Colors.blue,
                      ),
                      _buildInfoItem(
                        '📦 Заказы',
                        'Всегда актуальная с сервера',
                        Colors.blue,
                      ),
                      _buildInfoItem(
                        '📱 SMS коды',
                        'Никогда не кешировать',
                        Colors.red,
                      ),
                      _buildInfoItem(
                        '🔍 Поисковые запросы',
                        'Кеш 5 минут',
                        Colors.green,
                      ),
                    ],
                  ),
                ),

                // Кнопки управления
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    border:
                        Border(top: BorderSide(color: Colors.grey, width: 0.5)),
                  ),
                  child: Column(
                    children: [
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: () async {
                            final confirm = await showDialog<bool>(
                              context: context,
                              builder: (context) => AlertDialog(
                                title: const Text('Очистить кеш товаров?'),
                                content: const Text(
                                  'Товары будут загружаться заново при следующем просмотре.',
                                ),
                                actions: [
                                  TextButton(
                                    onPressed: () =>
                                        Navigator.pop(context, false),
                                    child: const Text('Отмена'),
                                  ),
                                  ElevatedButton(
                                    onPressed: () =>
                                        Navigator.pop(context, true),
                                    child: const Text('Очистить'),
                                  ),
                                ],
                              ),
                            );

                            if (confirm == true) {
                              await ApiService.clearProductsCache();
                              await _loadCacheStats();
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                      content: Text('Кеш товаров очищен')),
                                );
                              }
                            }
                          },
                          icon: const Icon(Icons.inventory),
                          label: const Text('Очистить кеш товаров'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.orange,
                            foregroundColor: Colors.white,
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: () async {
                            final confirm = await showDialog<bool>(
                              context: context,
                              builder: (context) => AlertDialog(
                                title: const Text('Очистить весь кеш?'),
                                content: const Text(
                                  'Все данные будут загружаться заново. Это может замедлить работу приложения.',
                                ),
                                actions: [
                                  TextButton(
                                    onPressed: () =>
                                        Navigator.pop(context, false),
                                    child: const Text('Отмена'),
                                  ),
                                  ElevatedButton(
                                    onPressed: () =>
                                        Navigator.pop(context, true),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: Colors.red,
                                    ),
                                    child: const Text('Очистить все'),
                                  ),
                                ],
                              ),
                            );

                            if (confirm == true) {
                              await ApiService.clearAllCache();
                              await _loadCacheStats();
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                      content: Text('Весь кеш очищен')),
                                );
                              }
                            }
                          },
                          icon: const Icon(Icons.delete_sweep),
                          label: const Text('Очистить весь кеш'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.red,
                            foregroundColor: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildInfoItem(String title, String subtitle, Color color) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: color,
          child: const Icon(
            Icons.info_outline,
            color: Colors.white,
            size: 20,
          ),
        ),
        title: Text(title),
        subtitle: Text(subtitle),
        dense: true,
      ),
    );
  }
}
