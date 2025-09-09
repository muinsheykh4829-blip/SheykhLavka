import 'package:flutter/material.dart';
import 'dart:io';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../models/cart_model.dart';
import 'address_screen.dart';
import '../widgets/banner_carousel.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'registration_screen.dart';
import '../widgets/categories_sheet.dart';
import '../widgets/search_pill.dart';
import '../widgets/cart_summary_bar.dart';
import '../theme.dart';
import '../widgets/modern_loader.dart';
import 'cart_screen.dart';
import '../models/product.dart' as pm;
import 'order_history_screen.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import '../config/api_config.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  // Приветственный диалог после регистрации убран по требованию
  String? _homeAvatarPath;
  List<pm.Product> _products = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadHomeAvatar();
    _loadHomeData();
  }

  Future<void> _loadHomeAvatar() async {
    // Попытка загрузить актуальные данные профиля с сервера
    try {
      final result = await ApiService.getProfile();
      if (result['success'] == true && result['data'] != null) {
        final user = result['data']['user'];
        final prefs = await SharedPreferences.getInstance();
        String? serverAvatar = user['avatar'];
        if (serverAvatar != null && serverAvatar.toString().isNotEmpty) {
          await prefs.setString('profile_avatar', serverAvatar);
          setState(() => _homeAvatarPath = serverAvatar);
          return;
        } else {
          // Если сервер не вернул аватар – используем локальный сохранённый
          final local = prefs.getString('profile_avatar');
          if (mounted) setState(() => _homeAvatarPath = local);
          return;
        }
      }
    } catch (e) {
      // Игнорируем ошибку загрузки
    }

    // Fallback: загружаем из локального хранилища
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        _homeAvatarPath = prefs.getString('profile_avatar');
      });
    }
  }

  Future<void> _loadHomeData() async {
    try {
      // Теперь баннеры загружаются непосредственно в BannerCarousel
      final products = await ApiService.getProducts();

      setState(() {
        // Безопасная проверка типов для продуктов
        final productsData = products['data'];
        _products = (productsData is List ? productsData : <dynamic>[])
            .map((json) => pm.Product.fromJson(json))
            .toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      // В случае ошибки можно показать снекбар или использовать дефолтные данные
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Ошибка загрузки данных: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
// ...импорты...

    return Scaffold(
      backgroundColor: AppColors.background,
      body: _isLoading
          ? const Center(child: ModernLoader(label: 'Загружаем для вас...'))
          : Stack(
              children: [
                // Новая карусель баннеров с API и кэшем
                const BannerCarousel(),

                // Кнопка "Мои заказы" слева вверху
                Positioned(
                  top: MediaQuery.of(context).padding.top + 8,
                  left: 16,
                  child: InkWell(
                    borderRadius: BorderRadius.circular(30),
                    onTap: _openOrdersWithSlideFromLeft,
                    child: CircleAvatar(
                      radius: 30,
                      backgroundColor: Colors.white.withValues(alpha: 0.2),
                      child: const Icon(
                        Icons.shopping_bag_outlined,
                        color: Color(0xFF22A447),
                        size: 30,
                      ),
                    ),
                  ),
                ),

                Positioned(
                  top: MediaQuery.of(context).padding.top + 8,
                  right: 16,
                  child: InkWell(
                    borderRadius: BorderRadius.circular(28),
                    onTap: _openProfileWithSlide,
                    child: CircleAvatar(
                      radius: 30,
                      backgroundColor: Colors.white.withValues(alpha: 0.2),
                      backgroundImage:
                          _buildAvatarImageProvider(_homeAvatarPath),
                      child: _homeAvatarPath == null
                          ? const Icon(Icons.person_outline,
                              color: Color(0xFF22A447), size: 32)
                          : null,
                    ),
                  ),
                ),

                // Центральная кнопка поиска убрана

                DraggableScrollableSheet(
                  initialChildSize: 0.35,
                  minChildSize: 0.2,
                  maxChildSize: 0.92,
                  snap: true,
                  builder: (context, controller) =>
                      CategoriesSheet(controller: controller),
                ),

                // Нижний ряд: поиск слева, корзина справа
                Positioned(
                  left: 20,
                  right: 20,
                  bottom: 24,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      SearchPill(
                        onTap: () async {
                          await showModalBottomSheet(
                            context: context,
                            isScrollControlled: true,
                            showDragHandle: true,
                            backgroundColor: Colors.white,
                            builder: (_) => FractionallySizedBox(
                              heightFactor: 0.85,
                              child: ProductSearchSheet(
                                products: _products,
                                onChoose: (p, {double? weight}) => context
                                    .read<CartModel>()
                                    .add(p, weight: weight),
                              ),
                            ),
                          );
                        },
                      ),
                      CartSummaryBar(
                        onTap: () async {
                          await showModalBottomSheet(
                            context: context,
                            isScrollControlled: true,
                            showDragHandle: true,
                            backgroundColor: Colors.transparent,
                            builder: (_) => const FractionallySizedBox(
                              heightFactor: 0.85,
                              child: CartScreen(),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }

  Future<void> _openProfileWithSlide() async {
    await Navigator.of(context).push(_slideRightRoute(const ProfileScreen()));
    if (mounted) await _loadHomeAvatar();
  }

  ImageProvider? _buildAvatarImageProvider(String? path) {
    if (path == null || path.isEmpty) return null;
    // Локальный файл
    if (path.startsWith('/') || path.contains(RegExp(r'^[A-Za-z]:\\'))) {
      final file = File(path);
      if (file.existsSync()) return FileImage(file);
    }
    // Относительный uploads/storage -> абсолютный URL
    if (path.startsWith('uploads/') ||
        path.startsWith('/uploads/') ||
        path.startsWith('storage/') ||
        path.startsWith('/storage/')) {
      final cleaned = path.startsWith('/') ? path.substring(1) : path;
      return NetworkImage(ApiConfig.fileUrl(cleaned));
    }
    // Уже абсолютный
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return NetworkImage(path);
    }
    return null;
  }

  Route _slideRightRoute(Widget page) {
    return PageRouteBuilder(
      transitionDuration: const Duration(milliseconds: 320),
      reverseTransitionDuration: const Duration(milliseconds: 280),
      pageBuilder: (context, animation, secondaryAnimation) => page,
      transitionsBuilder: (context, animation, secondaryAnimation, child) {
        final curved = CurvedAnimation(
            parent: animation,
            curve: Curves.easeOutCubic,
            reverseCurve: Curves.easeInCubic);
        final offsetAnim =
            Tween<Offset>(begin: const Offset(1.0, 0.0), end: Offset.zero)
                .animate(curved);
        final fade = CurvedAnimation(
            parent: animation,
            curve: Curves.easeOut,
            reverseCurve: Curves.easeIn);
        return SlideTransition(
          position: offsetAnim,
          child: FadeTransition(opacity: fade, child: child),
        );
      },
    );
  }

  Future<void> _openOrdersWithSlideFromLeft() async {
    await Navigator.of(context)
        .push(_slideLeftRoute(const OrderHistoryScreen()));
  }

  Route _slideLeftRoute(Widget page) {
    return PageRouteBuilder(
      transitionDuration: const Duration(milliseconds: 320),
      reverseTransitionDuration: const Duration(milliseconds: 280),
      pageBuilder: (context, animation, secondaryAnimation) => page,
      transitionsBuilder: (context, animation, secondaryAnimation, child) {
        final curved = CurvedAnimation(
            parent: animation,
            curve: Curves.easeOutCubic,
            reverseCurve: Curves.easeInCubic);
        final offsetAnim =
            Tween<Offset>(begin: const Offset(-1.0, 0.0), end: Offset.zero)
                .animate(curved);
        final fade = CurvedAnimation(
            parent: animation,
            curve: Curves.easeOut,
            reverseCurve: Curves.easeIn);
        return SlideTransition(
          position: offsetAnim,
          child: FadeTransition(opacity: fade, child: child),
        );
      },
    );
  }
}

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  String _firstName = '';
  String _lastName = '';
  String? _avatarPath;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    // Сначала пытаемся загрузить данные с сервера
    try {
      final result = await ApiService.getProfile();

      if (result['success'] == true && result['data'] != null) {
        final user = result['data']['user'];

        // Сохраняем данные локально для быстрого доступа
        final prefs = await SharedPreferences.getInstance();

        String firstName = (user['first_name'] ?? '').toString().trim();
        String lastName = (user['last_name'] ?? '').toString().trim();
        String? avatarPath = user['avatar'];

        // Сохраняем актуальные данные с сервера
        await prefs.setString('profile_first_name', firstName);
        await prefs.setString('profile_last_name', lastName);
        if (avatarPath != null) {
          await prefs.setString('profile_avatar', avatarPath);
        }
        if (user['phone'] != null) {
          await prefs.setString('phone', user['phone']);
        }
        if (user['gender'] != null) {
          await prefs.setString('profile_gender', user['gender']);
        }
        if (user['id'] != null) {
          await prefs.setString('user_id', user['id'].toString());
        }

        // Если сервер не дал аватар – пробуем локальный
        if (avatarPath == null || avatarPath.isEmpty) {
          final local = await SharedPreferences.getInstance();
          avatarPath = local.getString('profile_avatar');
        }
        if (mounted) {
          setState(() {
            _firstName = firstName;
            _lastName = lastName;
            _avatarPath = avatarPath;
          });
        }

        return; // Успешно загрузили с сервера
      }
    } catch (e) {
      print('Ошибка загрузки профиля с сервера: $e');
    }

    // Fallback: загружаем из локального хранилища
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      final fn = prefs.getString('profile_first_name');
      final ln = prefs.getString('profile_last_name');
      if (fn != null && fn.trim().isNotEmpty) _firstName = fn.trim();
      if (ln != null && ln.trim().isNotEmpty) _lastName = ln.trim();
      // миграция старого объединённого ключа
      if ((fn == null || fn.isEmpty) && (ln == null || ln.isEmpty)) {
        final legacy = prefs.getString('profile_name');
        if (legacy != null && legacy.trim().isNotEmpty) {
          final parts = legacy.trim().split(RegExp(r'\s+'));
          if (parts.isNotEmpty) _firstName = parts.first;
          if (parts.length > 1) {
            _lastName = parts.sublist(1).join(' ');
            prefs.setString('profile_first_name', _firstName);
            prefs.setString('profile_last_name', _lastName);
          } else {
            prefs.setString('profile_first_name', _firstName);
          }
        }
      }
      _avatarPath = prefs.getString('profile_avatar');
    });
    // убран локальный индикатор загрузки имени
  }

  Future<void> _openEdit() async {
    await Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const EditProfileScreen()),
    );
    await _loadProfile();
  }

  @override
  Widget build(BuildContext context) {
    final fullNameParts = [
      if (_firstName.trim().isNotEmpty) _firstName.trim(),
      if (_lastName.trim().isNotEmpty) _lastName.trim(),
    ];
    final fullName = fullNameParts.join(' ').trim();
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        elevation: 0,
        backgroundColor: const Color(0xFFF5F5F7),
        foregroundColor: Colors.black87,
        surfaceTintColor: Colors.transparent,
        title: const Text('Профиль',
            style: TextStyle(fontWeight: FontWeight.w600)),
        centerTitle: true,
      ),
      body: RefreshIndicator(
        onRefresh: _loadProfile,
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            Container(
              padding: const EdgeInsets.fromLTRB(20, 32, 20, 32),
              decoration: const BoxDecoration(
                color: Color(0xFFF5F5F7),
              ),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: _openEdit,
                    child: CircleAvatar(
                      radius: 44,
                      backgroundColor: const Color(0xFFE0E0E0),
                      backgroundImage: _avatarPath != null
                          ? FileImage(File(_avatarPath!))
                          : null,
                      child: _avatarPath == null
                          ? const Icon(Icons.person,
                              size: 50, color: Colors.white70)
                          : null,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (fullName.isNotEmpty)
                          Text(
                            fullName,
                            style: const TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.w400,
                              letterSpacing: 0.2,
                            ),
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),
            // Меню
            _ProfileMenuItem(
              icon: Icons.location_on_outlined,
              text: 'Мои адреса',
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const AddressScreen()),
              ),
            ),
            _ProfileMenuItem(
              icon: Icons.shopping_bag_outlined,
              text: 'Мои заказы',
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const OrderHistoryScreen()),
              ),
            ),
            _ProfileMenuItem(
              icon: Icons.person_outline,
              text: 'Мои данные',
              onTap: _openEdit,
            ),
            _ProfileMenuItem(
              icon: Icons.notifications_none,
              text: 'Уведомления',
              onTap: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                      content: Text('Экран уведомлений в разработке')),
                );
              },
            ),
            _ProfileMenuItem(
              icon: Icons.headset_mic_outlined,
              text: 'Служба поддержка',
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const SupportScreen()),
              ),
            ),
            _ProfileMenuItem(
              icon: Icons.redeem_outlined,
              text: 'Промо код',
              onTap: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                      content: Text('Раздел промо-кодов в разработке')),
                );
              },
            ),
            _ProfileMenuItem(
              icon: Icons.logout_outlined,
              text: 'Выйти',
              isDestructive: true,
              onTap: _performLogout,
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Future<void> _performLogout() async {
    await ApiService.removeToken();
    if (!mounted) return;
    Provider.of<AuthProvider>(context, listen: false).logout();
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const RegistrationScreen()),
      (route) => false,
    );
  }
}

// Унифицированный пункт меню нового стиля
class _ProfileMenuItem extends StatelessWidget {
  final IconData icon;
  final String text;
  final VoidCallback? onTap;
  final bool isDestructive;
  // размеры фиксированы, убрали опциональные параметры чтобы не было warning

  const _ProfileMenuItem({
    required this.icon,
    required this.text,
    this.onTap,
    this.isDestructive = false,
  });

  @override
  Widget build(BuildContext context) {
    final color = isDestructive ? Colors.red : const Color(0xFF222222);

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(18),
          color: Colors.white,
          border: Border.all(
            color: isDestructive
                ? Colors.red.withOpacity(0.18)
                : const Color(0xFFE9E9EC),
            width: 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 14,
              spreadRadius: 1,
              offset: const Offset(0, 4),
            ),
            BoxShadow(
              color: Colors.black.withOpacity(0.02),
              blurRadius: 2,
              offset: const Offset(0, 1),
            ),
          ],
        ),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            borderRadius: BorderRadius.circular(18),
            onTap: onTap,
            splashColor: const Color(0xFF03A84E).withOpacity(0.08),
            highlightColor: Colors.black.withOpacity(0.03),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
              child: Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      gradient: isDestructive
                          ? LinearGradient(colors: [
                              Colors.red.withOpacity(0.15),
                              Colors.red.withOpacity(0.05)
                            ])
                          : const LinearGradient(
                              colors: [Color(0xFFF2FDF7), Color(0xFFE9FCEE)],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                      shape: BoxShape.circle,
                      border: Border.all(
                          color: isDestructive
                              ? Colors.red.withOpacity(0.25)
                              : const Color(0xFFBFEFD3),
                          width: 1),
                    ),
                    child: Icon(icon,
                        color: isDestructive
                            ? Colors.red
                            : const Color(0xFF03A84E),
                        size: 26),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Text(
                      text,
                      style: TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.w500,
                        color: color,
                        letterSpacing: 0.2,
                      ),
                    ),
                  ),
                  Icon(Icons.chevron_right,
                      color: Colors.grey.shade400, size: 22),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// Удалены старые _ProfileTopButton и _ProfileListTile (заменены новым стилем)

// Пустой экран настроек
class EmptySettingsScreen extends StatelessWidget {
  const EmptySettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Настройка'),
        backgroundColor: const Color(0xFFF5F5F7),
        elevation: 0,
        foregroundColor: Colors.black87,
      ),
      backgroundColor: Colors.white,
      body: const Center(
        child: Text(
          'Пусто',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.w400),
        ),
      ),
    );
  }
}

// --- Экран "Мои данные" (Edit Profile) ---
class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _firstNameCtrl = TextEditingController();
  final _lastNameCtrl = TextEditingController();
  final _ageCtrl = TextEditingController();
  String? _gender; // 'male' | 'female'
  String? _avatarPath; // локальный путь

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      // Загружаем данные профиля с сервера
      final result = await ApiService.getProfile();

      if (result['success'] == true && result['data'] != null) {
        final user = result['data']['user'];
        setState(() {
          _firstNameCtrl.text = user['first_name'] ?? '';
          _lastNameCtrl.text = user['last_name'] ?? '';
          _ageCtrl.text = ''; // Пока убираем возраст
          _gender = user['gender'];
          _avatarPath = user['avatar'];
        });
      } else {
        // Fallback на локальные данные
        await _loadFromLocal();
      }
    } catch (e) {
      // При ошибке загружаем из локального хранилища
      await _loadFromLocal();
    }
  }

  Future<void> _loadFromLocal() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _firstNameCtrl.text = prefs.getString('profile_first_name') ?? '';
      _lastNameCtrl.text = prefs.getString('profile_last_name') ?? '';
      _ageCtrl.text = prefs.getInt('profile_age')?.toString() ?? '';
      _gender = prefs.getString('profile_gender');
      _avatarPath = prefs.getString('profile_avatar');
    });
  }

  Future<void> _pickAvatar() async {
    final picker = ImagePicker();
    final img =
        await picker.pickImage(source: ImageSource.gallery, imageQuality: 70);
    if (img != null) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('profile_avatar', img.path);
      setState(() => _avatarPath = img.path);
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    try {
      // Сохраняем данные на сервере
      final result = await ApiService.updateProfile(
        firstName: _firstNameCtrl.text.trim(),
        lastName: _lastNameCtrl.text.trim(),
        gender: _gender ?? '',
        avatarPath: _avatarPath,
      );

      if (result['success'] == true) {
        // Дополнительно сохраняем локально для быстрого доступа
        await _saveToLocal();

        if (!mounted) return;
        // Убираем уведомление о успешном сохранении
        Navigator.of(context).pop();
      } else {
        // Показываем ошибку
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Ошибка сохранения')),
        );
      }
    } catch (e) {
      // При ошибке сохраняем хотя бы локально
      await _saveToLocal();
      if (!mounted) return;
      // Убираем уведомление - сохраняем тихо
      Navigator.of(context).pop();
    }
  }

  Future<void> _saveToLocal() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('profile_first_name', _firstNameCtrl.text.trim());
    await prefs.setString('profile_last_name', _lastNameCtrl.text.trim());
    await prefs.setString('profile_gender', _gender ?? '');
    final age = int.tryParse(_ageCtrl.text.trim());
    if (age != null) await prefs.setInt('profile_age', age);
    if (_avatarPath != null) {
      await prefs.setString('profile_avatar', _avatarPath!);
    }
  }

  @override
  void dispose() {
    _firstNameCtrl.dispose();
    _lastNameCtrl.dispose();
    _ageCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final imageProvider = _buildAvatarImageProvider(_avatarPath);
    final avatar = CircleAvatar(
      radius: 48,
      backgroundColor: const Color(0xFFE0E0E0),
      backgroundImage: imageProvider,
      child: imageProvider == null
          ? const Icon(Icons.person, size: 48, color: Colors.white70)
          : null,
    );
    return Scaffold(
      appBar: AppBar(
        title: const Text('Мои данные'),
        actions: [
          PopupMenuButton<String>(
            onSelected: (v) async {
              if (v == 'clear') {
                final prefs = await SharedPreferences.getInstance();
                await prefs.remove('profile_name'); // legacy
                await prefs.remove('profile_first_name');
                await prefs.remove('profile_last_name');
                await prefs.remove('profile_gender');
                await prefs.remove('profile_age');
                await prefs.remove('profile_avatar');
                if (mounted) {
                  setState(() {
                    _firstNameCtrl.clear();
                    _lastNameCtrl.clear();
                    _ageCtrl.clear();
                    _gender = null;
                    _avatarPath = null;
                  });
                }
              }
            },
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'clear', child: Text('Сбросить')),
            ],
          )
        ],
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Center(
              child: Stack(
                alignment: Alignment.bottomRight,
                children: [
                  avatar,
                  Positioned(
                    bottom: 4,
                    right: 4,
                    child: InkWell(
                      onTap: _pickAvatar,
                      borderRadius: BorderRadius.circular(18),
                      child: Container(
                        decoration: const BoxDecoration(
                          color: Colors.white,
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(color: Colors.black12, blurRadius: 4)
                          ],
                        ),
                        padding: const EdgeInsets.all(6),
                        child: const Icon(Icons.camera_alt, size: 20),
                      ),
                    ),
                  )
                ],
              ),
            ),
            const SizedBox(height: 28),
            TextFormField(
              controller: _firstNameCtrl,
              textCapitalization: TextCapitalization.words,
              decoration: const InputDecoration(
                labelText: 'Имя',
                filled: true,
                fillColor: Color(0xFFF5F5F5),
                border: OutlineInputBorder(
                    borderSide: BorderSide.none,
                    borderRadius: BorderRadius.all(Radius.circular(12))),
              ),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Введите имя' : null,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _lastNameCtrl,
              textCapitalization: TextCapitalization.words,
              decoration: const InputDecoration(
                labelText: 'Фамилия',
                filled: true,
                fillColor: Color(0xFFF5F5F5),
                border: OutlineInputBorder(
                    borderSide: BorderSide.none,
                    borderRadius: BorderRadius.all(Radius.circular(12))),
              ),
            ),
            const SizedBox(height: 16),
            InputDecorator(
              decoration: const InputDecoration(
                labelText: 'Пол',
                filled: true,
                fillColor: Color(0xFFF5F5F5),
                border: OutlineInputBorder(
                    borderSide: BorderSide.none,
                    borderRadius: BorderRadius.all(Radius.circular(12))),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  value: _gender,
                  hint: const Text('Выберите пол'),
                  items: const [
                    DropdownMenuItem(value: 'male', child: Text('Мужской')),
                    DropdownMenuItem(value: 'female', child: Text('Женский')),
                  ],
                  onChanged: (v) => setState(() => _gender = v),
                ),
              ),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _ageCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Возраст',
                filled: true,
                fillColor: Color(0xFFF5F5F5),
                border: OutlineInputBorder(
                    borderSide: BorderSide.none,
                    borderRadius: BorderRadius.all(Radius.circular(12))),
              ),
              validator: (v) {
                if (v == null || v.trim().isEmpty) {
                  return null; // необязательное
                }
                final n = int.tryParse(v);
                if (n == null || n < 0 || n > 120) return 'Некорректно';
                return null;
              },
            ),
            const SizedBox(height: 32),
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF03A84E),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14)),
                ),
                onPressed: _save,
                child: const Text('Сохранить',
                    style:
                        TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  ImageProvider? _buildAvatarImageProvider(String? path) {
    if (path == null || path.isEmpty) return null;
    if (path.startsWith('/') || path.contains(RegExp(r'^[A-Za-z]:\\'))) {
      final f = File(path);
      if (f.existsSync()) return FileImage(f);
    }
    if (path.startsWith('uploads/') ||
        path.startsWith('/uploads/') ||
        path.startsWith('storage/') ||
        path.startsWith('/storage/')) {
      final cleaned = path.startsWith('/') ? path.substring(1) : path;
      return NetworkImage(ApiConfig.fileUrl(cleaned));
    }
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return NetworkImage(path);
    }
    return null;
  }
}

// Экран службы поддержки
class SupportScreen extends StatelessWidget {
  const SupportScreen({super.key});

  Future<void> _safeLaunch(List<Uri> uris, BuildContext context) async {
    for (final u in uris) {
      try {
        if (await canLaunchUrl(u)) {
          final ok = await launchUrl(u, mode: LaunchMode.externalApplication);
          if (ok) return; // успех
        }
      } catch (_) {}
    }
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Не удалось открыть приложение')),
      );
    }
  }

  void _call(BuildContext context) => _safeLaunch([
        Uri.parse('tel:920222020'),
        Uri.parse('tel:+992920222020'),
      ], context);

  void _whatsapp(BuildContext context) => _safeLaunch([
        Uri.parse('whatsapp://send?phone=992920222020'),
        Uri.parse('https://wa.me/992920222020'),
      ], context);

  void _telegram(BuildContext context) => _safeLaunch([
        Uri.parse('tg://resolve?domain=Lavak2020'),
        Uri.parse('https://t.me/Lavak2020'),
      ], context);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Служба поддержки')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          SupportTile(
            asset: null,
            icon: Icons.phone,
            title: 'Позвонить',
            subtitle: '920 22 20 20',
            onTap: () => _call(context),
          ),
          const SizedBox(height: 12),
          SupportTile(
            asset: 'assets/support/whatsapp.png',
            title: 'WhatsApp',
            subtitle: '+992 920 222 020',
            onTap: () => _whatsapp(context),
          ),
          const SizedBox(height: 12),
          SupportTile(
            asset: 'assets/support/telegram.png',
            title: 'Telegram',
            subtitle: 't.me/Lavak2020',
            onTap: () => _telegram(context),
          ),
        ],
      ),
    );
  }
}

class SupportTile extends StatelessWidget {
  final IconData? icon;
  final String? asset;
  final String title;
  final String subtitle;
  final VoidCallback onTap;
  const SupportTile(
      {super.key,
      this.icon,
      this.asset,
      required this.title,
      required this.subtitle,
      required this.onTap});
  @override
  Widget build(BuildContext context) {
    Widget leading;
    if (asset != null) {
      // Поддержка прозрачных PNG: даём цветной фон по сервису
      final lower = asset!.toLowerCase();
      Color bg = Colors.white;
      if (lower.contains('whatsapp')) bg = const Color(0xFF25D366);
      if (lower.contains('telegram')) bg = const Color(0xFF0088cc);
      leading = Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Padding(
          padding: const EdgeInsets.all(10),
          child: Image.asset(
            asset!,
            fit: BoxFit.contain,
            errorBuilder: (_, __, ___) =>
                const Icon(Icons.image_not_supported, color: Colors.white),
          ),
        ),
      );
    } else {
      leading = Icon(icon, size: 40, color: Colors.black87);
    }
    return Material(
      color: const Color(0xFFF5F5F5),
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
          child: Row(children: [
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFE3E3E3)),
              ),
              child: Center(child: leading),
            ),
            const SizedBox(width: 14),
            Expanded(
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                  Text(title,
                      style: const TextStyle(
                          fontWeight: FontWeight.w700, fontSize: 18)),
                  const SizedBox(height: 6),
                  Text(subtitle,
                      style:
                          const TextStyle(color: Colors.black54, fontSize: 15)),
                ])),
            const Icon(Icons.chevron_right, color: Colors.black45)
          ]),
        ),
      ),
    );
  }
}
