class Banner {
  final int id;
  final String title;
  final String? titleRu;
  final String image;
  final String? description;
  final String? descriptionRu;
  final String? linkUrl;
  final int sortOrder;
  final bool isActive;

  Banner({
    required this.id,
    required this.title,
    this.titleRu,
    required this.image,
    this.description,
    this.descriptionRu,
    this.linkUrl,
    this.sortOrder = 0,
    this.isActive = true,
  });

  factory Banner.fromJson(Map<String, dynamic> json) {
    return Banner(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      titleRu: json['title_ru'],
      image: json['image'] ?? '',
      description: json['description'],
      descriptionRu: json['description_ru'],
      linkUrl: json['link_url'],
      sortOrder: json['sort_order'] ?? 0,
      isActive: json['is_active'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'title_ru': titleRu,
      'image': image,
      'description': description,
      'description_ru': descriptionRu,
      'link_url': linkUrl,
      'sort_order': sortOrder,
      'is_active': isActive,
    };
  }

  // Получить локализованное название
  String getLocalizedTitle([String locale = 'ru']) {
    if (locale == 'ru' && titleRu != null && titleRu!.isNotEmpty) {
      return titleRu!;
    }
    return title;
  }

  // Получить локализованное описание
  String? getLocalizedDescription([String locale = 'ru']) {
    if (locale == 'ru' && descriptionRu != null && descriptionRu!.isNotEmpty) {
      return descriptionRu;
    }
    return description;
  }

  // Проверить, есть ли ссылка
  bool get hasLink => linkUrl != null && linkUrl!.isNotEmpty;
}
