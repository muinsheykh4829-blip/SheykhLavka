<?php

// Простой тест API для проверки delivery_type
$url = 'http://127.0.0.1:8000/api/v1/orders';

// Тестируем со стандартной доставкой
$data_standard = [
    'delivery_address' => 'Тестовый адрес',
    'delivery_phone' => '+992000000999',
    'delivery_name' => 'Тест Пользователь',
    'payment_method' => 'cash',
    'comment' => 'Тест стандартной доставки',
    'delivery_type' => 'standard'
];

// Тестируем с экспресс доставкой
$data_express = [
    'delivery_address' => 'Тестовый адрес',
    'delivery_phone' => '+992000000999',
    'delivery_name' => 'Тест Пользователь',
    'payment_method' => 'cash',
    'comment' => 'Тест экспресс доставки',
    'delivery_type' => 'express'
];

function testOrder($url, $data, $type) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "=== ТЕСТ $type ДОСТАВКИ ===\n";
    echo "HTTP код: $http_code\n";
    
    if ($response) {
        $result = json_decode($response, true);
        if (isset($result['order'])) {
            echo "Заказ создан: " . $result['order']['order_number'] . "\n";
            echo "Товары: " . $result['order']['subtotal'] . " драм\n";
            echo "Доставка: " . $result['order']['delivery_fee'] . " драм\n";
            echo "Всего: " . $result['order']['total'] . " драм\n";
            echo "Тип доставки: " . ($result['order']['delivery_type'] ?? 'не указан') . "\n";
        } else {
            echo "Ошибка: " . ($result['message'] ?? 'Неизвестная ошибка') . "\n";
        }
    } else {
        echo "Нет ответа от сервера\n";
    }
    echo "\n";
}

// Запускаем тесты
testOrder($url, $data_standard, 'СТАНДАРТНОЙ');
testOrder($url, $data_express, 'ЭКСПРЕСС');
