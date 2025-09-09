<?php

echo "=== Тестирование API после очистки категорий ===\n\n";

$baseUrl = 'http://127.0.0.1:8000/api/v1';

// Тестируем API категорий
echo "📂 Тестируем /api/v1/categories:\n";
$categoriesResponse = file_get_contents($baseUrl . '/categories');
// Убираем возможные лишние символы в начале ответа
$categoriesResponse = preg_replace('/^[^{]*/', '', $categoriesResponse);
$categories = json_decode($categoriesResponse, true);

if ($categories && isset($categories['success'])) {
    if ($categories['success']) {
        $count = count($categories['data'] ?? []);
        echo "✅ API работает, категорий: $count\n";
        if ($count === 0) {
            echo "✅ Отлично! Категории очищены\n";
        } else {
            echo "❌ Еще есть категории:\n";
            foreach ($categories['data'] as $cat) {
                echo "  - {$cat['name_ru']} (ID: {$cat['id']})\n";
            }
        }
    } else {
        echo "❌ API вернул ошибку: " . ($categories['message'] ?? 'неизвестная ошибка') . "\n";
    }
} else {
    echo "❌ Некорректный ответ API\n";
    echo "Ответ: " . substr($categoriesResponse, 0, 200) . "\n";
}

echo "\n📦 Тестируем /api/v1/products:\n";
$productsResponse = file_get_contents($baseUrl . '/products');
// Убираем возможные лишние символы в начале ответа
$productsResponse = preg_replace('/^[^{]*/', '', $productsResponse);
$products = json_decode($productsResponse, true);

if ($products && isset($products['success'])) {
    if ($products['success']) {
        $count = count($products['data'] ?? []);
        echo "✅ API работает, продуктов: $count\n";
        if ($count === 0) {
            echo "✅ Отлично! Продукты очищены\n";
        }
    } else {
        echo "❌ API вернул ошибку: " . ($products['message'] ?? 'неизвестная ошибка') . "\n";
    }
} else {
    echo "❌ Некорректный ответ API\n";
    echo "Ответ: " . substr($productsResponse, 0, 200) . "\n";
}

echo "\n🎉 Теперь можно добавлять категории через веб-админ панель!\n";
echo "🌐 Админ панель: http://127.0.0.1:8000/admin\n";
