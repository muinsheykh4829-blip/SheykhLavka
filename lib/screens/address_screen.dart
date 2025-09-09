import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/cart_model.dart';
import '../services/api_service.dart';
import 'address_form_dialog.dart';

class AddressScreen extends StatefulWidget {
  const AddressScreen({super.key});

  @override
  State<AddressScreen> createState() => _AddressScreenState();
}

class _AddressScreenState extends State<AddressScreen> {
  String userPhone = '';
  late String selected;
  List<Map<String, dynamic>> addresses = [];
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    selected = '';
    _loadAddresses();
    _loadPhone();
  }

  Future<void> _loadPhone() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      userPhone = prefs.getString('user_phone') ?? '';
    });
  }

  Future<void> _loadAddresses() async {
    try {
      setState(() => isLoading = true);

      // Загрузить с сервера
      final response = await ApiService.getAddresses();

      if (response['success'] == true) {
        final serverAddresses = response['addresses'] as List<dynamic>;

        setState(() {
          addresses = serverAddresses
              .map((addr) => {
                    'id': addr['id'],
                    'street': addr['street'] ?? '',
                    'house_number': addr['house_number'] ?? '',
                    'apartment': addr['apartment'] ?? '',
                    'entrance': addr['entrance'] ?? '',
                    'floor': addr['floor'] ?? '',
                    'intercom': addr['intercom'] ?? '',
                    'comment': addr['comment'] ?? '',
                    'is_default': addr['is_default'] ?? false,
                    'full_address': _buildFullAddress(addr),
                  })
              .toList();

          // Найти адрес по умолчанию
          for (var addr in addresses) {
            if (addr['is_default'] == true) {
              selected = addr['full_address'];
              break;
            }
          }
        });
      } else {
        // Загрузить локально как fallback
        await _loadAddressesLocally();
      }
    } catch (e) {
      // Загрузить локально при ошибке
      await _loadAddressesLocally();
    } finally {
      setState(() => isLoading = false);
    }
  }

  Future<void> _loadAddressesLocally() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      final localAddresses = prefs.getStringList('user_addresses') ?? [];
      addresses = localAddresses
          .asMap()
          .entries
          .map((entry) => {
                'id': entry.key, // Используем индекс как ID
                'full_address': entry.value,
                'is_default':
                    entry.value == (prefs.getString('selected_address') ?? ''),
              })
          .toList();
      selected = prefs.getString('selected_address') ?? '';
    });
  }

  String _buildFullAddress(Map<String, dynamic> addr) {
    String result = addr['street'] ?? '';
    if ((addr['house_number'] ?? '').isNotEmpty) {
      result += ', дом ${addr['house_number']}';
    }
    if ((addr['apartment'] ?? '').isNotEmpty) {
      result += ', кв. ${addr['apartment']}';
    }
    if ((addr['entrance'] ?? '').isNotEmpty) {
      result += ', подъезд ${addr['entrance']}';
    }
    if ((addr['floor'] ?? '').isNotEmpty) {
      result += ', этаж ${addr['floor']}';
    }
    return result;
  }

  Future<void> _saveAddresses() async {
    final prefs = await SharedPreferences.getInstance();
    final addressStrings =
        addresses.map((addr) => addr['full_address'] as String).toList();
    await prefs.setStringList('user_addresses', addressStrings);
    await prefs.setString('selected_address', selected);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Мои адреса',
            style: TextStyle(fontWeight: FontWeight.bold)),
        centerTitle: true,
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.black),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      backgroundColor: Colors.white,
      body: isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF22A447)))
          : Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (userPhone.isNotEmpty) ...[
                    Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: Row(
                        children: [
                          const Icon(Icons.phone, color: Color(0xFF22A447)),
                          const SizedBox(width: 8),
                          Text(userPhone, style: const TextStyle(fontSize: 16)),
                        ],
                      ),
                    ),
                  ],
                  if (addresses.isNotEmpty)
                    Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF6F6F6),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListView.separated(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: addresses.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemBuilder: (context, i) {
                          final addr = addresses[i];
                          final fullAddress = addr['full_address'] as String;
                          final isSelected = fullAddress == selected;
                          return InkWell(
                            borderRadius: BorderRadius.circular(12),
                            onTap: () => setState(() => selected = fullAddress),
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 14),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? const Color(0xFFF2F2F2)
                                    : Colors.transparent,
                                borderRadius: BorderRadius.circular(12),
                                border: isSelected
                                    ? Border.all(
                                        color: const Color(0xFF22A447), width: 1.5)
                                    : null,
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.home, color: Color(0xFF22A447)),
                                  const SizedBox(width: 10),
                                  Expanded(
                                      child: Text(fullAddress,
                                          style:
                                              const TextStyle(fontSize: 16))),
                                  IconButton(
                                    icon: const Icon(Icons.delete_outline,
                                        color: Colors.redAccent),
                                    tooltip: 'Удалить',
                                    onPressed: () async {
                                      // Если есть ID сервера - удалить с сервера
                                      if (addr['id'] != null &&
                                          addr['id'] is int) {
                                        try {
                                          await ApiService.deleteAddress(
                                              addr['id']);
                                        } catch (e) {
                                          // Игнорируем ошибки удаления с сервера
                                        }
                                      }

                                      setState(() {
                                        addresses.removeAt(i);
                                        if (selected == fullAddress) {
                                          selected = '';
                                        }
                                      });
                                      _saveAddresses();
                                    },
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                  const SizedBox(height: 24),
                  InkWell(
                    borderRadius: BorderRadius.circular(12),
                    onTap: () async {
                      final res = await showDialog<String>(
                        context: context,
                        builder: (context) => const AddressFormDialog(),
                      );
                      if (res != null && res.trim().isNotEmpty) {
                        setState(() {
                          // Добавляем как новый адрес в локальный список
                          addresses.insert(0, {
                            'id': null, // Нет ID сервера пока
                            'full_address': res.trim(),
                            'is_default': false,
                          });
                          selected = res.trim();
                        });
                        context.read<CartModel>().setAddress(res.trim());
                        _saveAddresses();

                        // Перезагрузить адреса с сервера, чтобы получить актуальные данные
                        _loadAddresses();
                      }
                    },
                    child: const Padding(
                      padding: EdgeInsets.symmetric(
                          vertical: 8, horizontal: 4),
                      child: Row(
                        children: [
                          Icon(Icons.add, color: Colors.black87),
                          SizedBox(width: 8),
                          Text('Добавить адрес',
                              style: TextStyle(fontSize: 16)),
                        ],
                      ),
                    ),
                  ),
                  const Spacer(),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF22A447),
                        foregroundColor: Colors.white,
                        textStyle: const TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 17),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      onPressed: () {
                        context.read<CartModel>().setAddress(selected);
                        _saveAddresses();
                        Navigator.pop(context);
                      },
                      child: const Text('Готово'),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
