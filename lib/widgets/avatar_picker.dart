import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

class AvatarPicker extends StatefulWidget {
  final double radius;
  final String? initialAsset;
  const AvatarPicker({super.key, this.radius = 35, this.initialAsset});

  @override
  State<AvatarPicker> createState() => _AvatarPickerState();
}

class _AvatarPickerState extends State<AvatarPicker> {
  File? _imageFile;

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(source: ImageSource.gallery);
    if (pickedFile != null) {
      setState(() {
        _imageFile = File(pickedFile.path);
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: _pickImage,
      child: CircleAvatar(
        radius: widget.radius + 3,
        backgroundColor: Colors.white,
        child: CircleAvatar(
          radius: widget.radius,
          backgroundImage: _imageFile != null
              ? FileImage(_imageFile!)
              : (widget.initialAsset != null
                  ? AssetImage(widget.initialAsset!) as ImageProvider
                  : null),
          child: _imageFile == null && widget.initialAsset == null
              ? const Icon(Icons.person, size: 40, color: Colors.grey)
              : null,
        ),
      ),
    );
  }
}
