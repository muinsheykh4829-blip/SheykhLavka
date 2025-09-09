import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mask_text_input_formatter/mask_text_input_formatter.dart';
import '../services/api_service.dart';

class AddressFormDialog extends StatefulWidget {
  const AddressFormDialog({super.key});

  @override
  State<AddressFormDialog> createState() => _AddressFormDialogState();
}

class _AddressFormDialogState extends State<AddressFormDialog> {
  final _tajikPhoneMask = MaskTextInputFormatter(
    mask: '+992 ## ###-##-##',
    filter: {"#": RegExp(r'[0-9]')},
    type: MaskAutoCompletionType.lazy,
  );
  final _addressController = TextEditingController();
  final _entranceController = TextEditingController();
  final _doorphoneController = TextEditingController();
  final _apartmentController = TextEditingController();
  final _floorController = TextEditingController();
  final _phoneController = TextEditingController();

  @override
  void dispose() {
    _addressController.dispose();
    _entranceController.dispose();
    _doorphoneController.dispose();
    _apartmentController.dispose();
    _floorController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      backgroundColor: Colors.white,
      title: const Text('Добавить адрес'),
      content: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Телефон'),
            const SizedBox(height: 4),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              inputFormatters: [_tajikPhoneMask],
              decoration: const InputDecoration(
                hintText: '+992 XX XXX-XX-XX',
                filled: true,
                fillColor: Color(0xFFF6F6F6),
                border: OutlineInputBorder(
                    borderSide: BorderSide.none,
                    borderRadius: BorderRadius.all(Radius.circular(8))),
                contentPadding:
                    EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              ),
            ),
            const SizedBox(height: 12),
            const Text('Улица или Микрорайон'),
            const SizedBox(height: 4),
            TextField(
              controller: _addressController,
              decoration: const InputDecoration(
                hintText: 'Введите улицу или микрорайон',
                filled: true,
                fillColor: Color(0xFFF6F6F6),
                border: OutlineInputBorder(
                    borderSide: BorderSide.none,
                    borderRadius: BorderRadius.all(Radius.circular(8))),
                contentPadding:
                    EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Подъезд'),
                      const SizedBox(height: 4),
                      TextField(
                        controller: _entranceController,
                        decoration: const InputDecoration(
                          hintText: 'Подъезд',
                          filled: true,
                          fillColor: Color(0xFFF6F6F6),
                          border: OutlineInputBorder(
                              borderSide: BorderSide.none,
                              borderRadius:
                                  BorderRadius.all(Radius.circular(8))),
                          contentPadding: EdgeInsets.symmetric(
                              horizontal: 12, vertical: 12),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Дом'),
                      const SizedBox(height: 4),
                      TextField(
                        controller: _doorphoneController,
                        decoration: const InputDecoration(
                          hintText: 'Дом',
                          filled: true,
                          fillColor: Color(0xFFF6F6F6),
                          border: OutlineInputBorder(
                              borderSide: BorderSide.none,
                              borderRadius:
                                  BorderRadius.all(Radius.circular(8))),
                          contentPadding: EdgeInsets.symmetric(
                              horizontal: 12, vertical: 12),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Квартира'),
                      const SizedBox(height: 4),
                      TextField(
                        controller: _apartmentController,
                        decoration: const InputDecoration(
                          hintText: 'Квартира',
                          filled: true,
                          fillColor: Color(0xFFF6F6F6),
                          border: OutlineInputBorder(
                              borderSide: BorderSide.none,
                              borderRadius:
                                  BorderRadius.all(Radius.circular(8))),
                          contentPadding: EdgeInsets.symmetric(
                              horizontal: 12, vertical: 12),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Этаж'),
                      const SizedBox(height: 4),
                      TextField(
                        controller: _floorController,
                        decoration: const InputDecoration(
                          hintText: 'Этаж',
                          filled: true,
                          fillColor: Color(0xFFF6F6F6),
                          border: OutlineInputBorder(
                              borderSide: BorderSide.none,
                              borderRadius:
                                  BorderRadius.all(Radius.circular(8))),
                          contentPadding: EdgeInsets.symmetric(
                              horizontal: 12, vertical: 12),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Отмена'),
        ),
        ElevatedButton(
          onPressed: () async {
            final street = _addressController.text.trim();
            final house = _doorphoneController.text.trim();
            final apartment = _apartmentController.text.trim();
            final entrance = _entranceController.text.trim();
            final floor = _floorController.text.trim();
            final phone = _phoneController.text.trim();

            if (street.isNotEmpty && house.isNotEmpty && phone.isNotEmpty) {
              try {
                // Показать индикатор загрузки
                showDialog(
                  context: context,
                  barrierDismissible: false,
                  builder: (_) =>
                      const Center(child: CircularProgressIndicator()),
                );

                // Сохранить адрес на сервере
                final response = await ApiService.addAddress(
                  street: street,
                  houseNumber: house,
                  apartment: apartment.isEmpty ? null : apartment,
                  entrance: entrance.isEmpty ? null : entrance,
                  floor: floor.isEmpty ? null : floor,
                  type: 'home',
                  title: 'Дом',
                );

                // Убрать индикатор загрузки
                if (context.mounted) Navigator.pop(context);

                if (response['success'] == true) {
                  // Сохранить телефон локально
                  final prefs = await SharedPreferences.getInstance();
                  await prefs.setString('user_phone', phone);

                  // Закрыть диалог с результатом
                  if (context.mounted) {
                    Navigator.pop(context,
                        street + (house.isNotEmpty ? ", дом $house" : ""));
                  }
                } else {
                  // Показать ошибку, но всё равно сохранить локально
                  final prefs = await SharedPreferences.getInstance();
                  await prefs.setString('user_phone', phone);

                  if (context.mounted) {
                    Navigator.pop(context,
                        street + (house.isNotEmpty ? ", дом $house" : ""));
                  }
                }
              } catch (e) {
                // Убрать индикатор загрузки
                if (context.mounted) Navigator.pop(context);

                // Сохранить локально при ошибке
                final prefs = await SharedPreferences.getInstance();
                await prefs.setString('user_phone', phone);

                if (context.mounted) {
                  Navigator.pop(context,
                      street + (house.isNotEmpty ? ", дом $house" : ""));
                }
              }
            }
          },
          child: const Text('Добавить'),
        ),
      ],
    );
  }
}
