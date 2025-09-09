<?php

return [
    // Глобальное включение/выключение кэша API
    'enabled' => env('API_CACHE_ENABLED', true),

    // Режим тестирования (при включении используются сильно уменьшенные TTL)
    'test_mode' => env('API_CACHE_TEST_MODE', true),

    // Основные TTL (секунды) в рабочем режиме
    'ttls' => [
        'categories' => env('API_CACHE_TTL_CATEGORIES', 60 * 60 * 24),      // 24 часа
        'products'   => env('API_CACHE_TTL_PRODUCTS',   60 * 60),           // 1 час
        'banners'    => env('API_CACHE_TTL_BANNERS',    60 * 60 * 12),      // 12 часов
        'profile'    => env('API_CACHE_TTL_PROFILE',    60 * 30),           // 30 минут
        'search'     => env('API_CACHE_TTL_SEARCH',     60 * 5),            // 5 минут
    ],

    // Сокращённые TTL для тестирования (секунды)
    'test_ttls' => [
        'categories' => 2,
        'products'   => 2,
        'banners'    => 2,
        'profile'    => 2,
        'search'     => 2,
    ],
];
