import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'theme.dart';
import 'models/cart_model.dart';
import 'providers/auth_provider.dart';
import 'screens/splash_screen.dart';
import 'screens/registration_screen.dart';
import 'screens/home_screen.dart';

void main() {
  runApp(const App());
}

class App extends StatelessWidget {
  const App({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => CartModel()),
        ChangeNotifierProvider(create: (_) => AuthProvider()),
      ],
      child: MaterialApp(
        title: 'Sheykh Lavka',
        debugShowCheckedModeBanner: false,
        theme: buildTheme(),
        home: Consumer<AuthProvider>(
          builder: (context, authProvider, child) {
            // Показываем splash screen пока идет проверка авторизации
            if (authProvider.isLoading) {
              return SplashScreen(onFinish: () {});
            }

            // Показываем соответствующий экран в зависимости от статуса авторизации
            return authProvider.isAuthenticated
                ? const HomeScreen()
                : const RegistrationScreen();
          },
        ),
      ),
    );
  }
}

// Экран подтверждения кода будет добавлен позже
