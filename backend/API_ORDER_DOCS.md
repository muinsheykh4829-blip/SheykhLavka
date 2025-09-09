# Тестирование API для создания заказов

## Создание заказа через POST запрос

### Endpoint: POST /api/orders

**Headers:**
```
Content-Type: application/json
Authorization: Bearer {token}
```

**Body:**
```json
{
    "delivery_address": "г. Ташкент, ул. Амира Темура 15, кв. 25",
    "delivery_phone": "+998901234567",
    "delivery_name": "Иван Иванов",
    "delivery_time": null,
    "payment_method": "cash",
    "comment": "Домофон не работает, звонить в дверь"
}
```

### Пример запроса через curl:

```bash
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "delivery_address": "г. Ташкент, ул. Амира Темура 15, кв. 25",
    "delivery_phone": "+998901234567", 
    "delivery_name": "Иван Иванов",
    "payment_method": "cash",
    "comment": "Домофон не работает, звонить в дверь"
  }'
```

### Успешный ответ:
```json
{
    "success": true,
    "message": "Заказ успешно создан",
    "order": {
        "id": 1,
        "order_number": "SL202409022341",
        "user_id": 1,
        "status": "pending",
        "subtotal": 45000,
        "delivery_fee": 5000,
        "discount": 0,
        "total": 50000,
        "payment_method": "cash",
        "payment_status": "pending",
        "delivery_address": "г. Ташкент, ул. Амира Темура 15, кв. 25",
        "delivery_phone": "+998901234567",
        "delivery_name": "Иван Иванов",
        "comment": "Домофон не работает, звонить в дверь",
        "items": [
            {
                "id": 1,
                "product_id": 1,
                "quantity": 2,
                "price": 15000,
                "total": 30000,
                "product": {
                    "name": "Плов",
                    "price": 15000
                }
            }
        ]
    }
}
```

### Ошибки валидации:
```json
{
    "success": false,
    "message": "Ошибка валидации",
    "errors": {
        "delivery_address": ["Поле адрес доставки обязательно для заполнения"]
    }
}
```

## Процесс для Flutter приложения:

1. Пользователь добавляет товары в корзину
2. Переходит к оформлению заказа
3. Заполняет данные доставки
4. Нажимает "Оформить заказ"
5. Flutter отправляет POST запрос на /api/orders
6. Заказ сохраняется в базу данных
7. Заказ автоматически появляется в веб-админ панели
8. Админ может управлять статусом заказа

## Статусы заказа:
- **pending** - Ожидает (начальный статус)
- **confirmed** - Подтвержден
- **preparing** - Готовится  
- **ready** - Готов
- **delivering** - Доставляется
- **delivered** - Доставлен
- **cancelled** - Отменен
