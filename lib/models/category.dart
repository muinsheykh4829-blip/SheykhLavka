class Category {
  final int id;
  final String name;
  final String nameRu;
  final String slug;
  final String? icon;
  final String? description;
  final int sortOrder;
  final bool isActive;

  Category({
    required this.id,
    required this.name,
    required this.nameRu,
    required this.slug,
    this.icon,
    this.description,
    this.sortOrder = 0,
    this.isActive = true,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      nameRu: json['name_ru'] ?? json['name'] ?? '',
      slug: json['slug'] ?? '',
      icon: json['icon'],
      description: json['description'],
      sortOrder: json['sort_order'] ?? 0,
      isActive: json['is_active'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'name_ru': nameRu,
      'slug': slug,
      'icon': icon,
      'description': description,
      'sort_order': sortOrder,
      'is_active': isActive,
    };
  }

  // Получить локализованное название
  String getLocalizedName([String locale = 'ru']) {
    return locale == 'ru' ? nameRu : name;
  }
}
