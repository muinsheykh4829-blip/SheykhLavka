class User {
  final int id;
  final String name;
  final String phone;
  final String? email;
  final String? firstName;
  final String? lastName;
  final DateTime? phoneVerifiedAt;
  final bool isActive;

  User({
    required this.id,
    required this.name,
    required this.phone,
    this.email,
    this.firstName,
    this.lastName,
    this.phoneVerifiedAt,
    this.isActive = true,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      phone: json['phone'],
      email: json['email'],
      firstName: json['first_name'],
      lastName: json['last_name'],
      phoneVerifiedAt: json['phone_verified_at'] != null
          ? DateTime.parse(json['phone_verified_at'])
          : null,
      isActive: json['is_active'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'phone': phone,
      'email': email,
      'first_name': firstName,
      'last_name': lastName,
      'phone_verified_at': phoneVerifiedAt?.toIso8601String(),
      'is_active': isActive,
    };
  }
}
