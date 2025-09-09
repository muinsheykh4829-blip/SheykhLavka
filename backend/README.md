# Sheykh Lavka Backend

Laravel-based backend для мобильного приложения продуктового магазина.

## ✅ Готово и работает

### Структура базы данных
- **users** - пользователи с аутентификацией по SMS
- **categories** - категории товаров
- **products** - товары с изображениями и ценами
- **orders** - заказы пользователей
- **order_items** - позиции заказов
- **banners** - баннеры для главной страницы
- **carts** - корзина пользователя
- **addresses** - адреса доставки

### API Endpoints

#### 🔐 Аутентификация
- `POST /api/v1/auth/register` - регистрация пользователя
- `POST /api/v1/auth/verify-code` - подтверждение SMS кода
- `POST /api/v1/auth/login` - вход в систему
- `POST /api/v1/auth/logout` - выход из системы

#### 📦 Каталог товаров
- `GET /api/v1/categories` - получить категории
- `GET /api/v1/products` - получить товары (с фильтрами)
- `GET /api/v1/products/{id}` - получить товар по ID
- `GET /api/v1/banners` - получить баннеры

#### 🛒 Корзина и заказы (требуют авторизации)
- `GET /api/v1/cart` - получить корзину
- `POST /api/v1/cart` - добавить в корзину
- `PUT /api/v1/cart/{id}` - обновить количество
- `DELETE /api/v1/cart/{id}` - удалить из корзины
- `POST /api/v1/orders` - создать заказ
- `GET /api/v1/orders` - получить заказы пользователя

#### 👤 Профиль пользователя
- `GET /api/v1/profile` - получить профиль
- `PUT /api/v1/profile` - обновить профиль
- `GET /api/v1/addresses` - получить адреса
- `POST /api/v1/addresses` - добавить адрес

## � SMS Подтверждение

### Режим разработки
- **Фиксированный код для всех пользователей: 123456**
- SMS не отправляются, код указывается в ответе регистрации
- Тестирование: http://127.0.0.1:8000/sms-test.html

### Настройки в .env
```env
SMS_DEVELOPMENT_MODE=true
SMS_DEVELOPMENT_CODE=123456
SMS_CODE_EXPIRES_MINUTES=10
SMS_PROVIDER=local
```

### Переход в продакшн
Для реальных SMS измените в `.env`:
```env
SMS_DEVELOPMENT_MODE=false
SMS_PROVIDER=twilio
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_FROM_NUMBER=your_number
```

## �🚀 Запуск проекта

### Требования
- PHP 8.1+
- MySQL 8.0+
- Composer

### Установка
```bash
# Переход в директорию бэкенда
cd c:\Users\user\Desktop\dastovka\backend

# Установка зависимостей
composer install

# Копирование конфигурации
cp .env.example .env

# Генерация ключа приложения
php artisan key:generate

# Настройка базы данных в .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sheykh_lavka
DB_USERNAME=root
DB_PASSWORD=

# Миграции и заполнение данными
php artisan migrate --seed

# Запуск сервера разработки
php artisan serve
```

### Тестирование API
1. Откройте браузер: http://127.0.0.1:8000/api-endpoints-test.html
2. Протестируйте все основные endpoints
3. Проверьте полный flow аутентификации

## 📱 Интеграция с Flutter

См. файл `FLUTTER_INTEGRATION.md` для:
- Примеров HTTP запросов
- Моделей данных Dart
- Настройки аутентификации
- Хранения токенов

### Базовый URL для Flutter
```dart
const String baseUrl = 'http://127.0.0.1:8000/api/v1';
```

## 📋 Структура ответов API

### Успешный ответ
```json
{
    "success": true,
    "message": "Описание операции",
    "data": { ... }
}
```

### Ошибка
```json
{
    "success": false,
    "message": "Описание ошибки",
    "errors": { ... } // для ошибок валидации
}
```

## 🔧 Конфигурация

### CORS
Настроен для работы с мобильными приложениями:
```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'], // в продакшене настроить правильно
'allowed_headers' => ['*'],
```

### Аутентификация
Используется Laravel Sanctum для API токенов:
- Время жизни токена: не ограничено
- Отзыв токенов при logout
- Множественные токены для разных устройств

## 📝 Логи и отладка

### Просмотр логов
```bash
tail -f storage/logs/laravel.log
```

### Очистка кеша
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 🗄️ База данных

### Подключение через SQLyog
- Host: 127.0.0.1
- Port: 3306
- Database: sheykh_lavka
- Username: root
- Password: (пустой)

### Сброс базы данных
```bash
php artisan migrate:fresh --seed
```

## 📖 Документация

- `API_DOCUMENTATION.md` - полное описание API
- `FLUTTER_INTEGRATION.md` - интеграция с Flutter
- Тестирование: http://127.0.0.1:8000/api-endpoints-test.html

---

**Статус проекта:** ✅ Готов к интеграции с Flutter приложением

**Следующие шаги:**
1. Настроить SMS-сервис для отправки кодов подтверждения
2. Добавить загрузку изображений товаров и аватаров
3. Реализовать систему push-уведомлений
4. Добавить админ-панель для управления контентом

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
