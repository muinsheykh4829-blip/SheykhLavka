# Тест API через PowerShell/curl

## 1. Регистрация пользователя
curl -X POST http://127.0.0.1:8000/api/v1/auth/register `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  -d '{
    "name": "Тест Пользователь",
    "phone": "+998901234567",
    "password": "123456"
  }'

## 2. Подтверждение кода (замените user_id и code на полученные)
curl -X POST http://127.0.0.1:8000/api/v1/auth/verify-code `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  -d '{
    "user_id": "1",
    "code": "123456"
  }'

## 3. Вход в систему
curl -X POST http://127.0.0.1:8000/api/v1/auth/login `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  -d '{
    "phone": "+998901234567",
    "password": "123456"
  }'

## 4. Получение профиля (замените YOUR_TOKEN на полученный токен)
curl -X GET http://127.0.0.1:8000/api/v1/user `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  -H "Authorization: Bearer YOUR_TOKEN"

## 5. Выход из системы
curl -X POST http://127.0.0.1:8000/api/v1/auth/logout `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  -H "Authorization: Bearer YOUR_TOKEN"

## 6. Тестирование публичных endpoint'ов

# Получить категории
curl -X GET http://127.0.0.1:8000/api/v1/categories `
  -H "Accept: application/json"

# Получить товары
curl -X GET http://127.0.0.1:8000/api/v1/products `
  -H "Accept: application/json"

# Поиск товаров
curl -X GET "http://127.0.0.1:8000/api/v1/products/search?q=яблоко" `
  -H "Accept: application/json"

# Получить баннеры
curl -X GET http://127.0.0.1:8000/api/v1/banners `
  -H "Accept: application/json"
