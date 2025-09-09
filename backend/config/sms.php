<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки для отправки SMS сообщений
    |
    */

    // Режим разработки - использовать фиксированный код
    'development_mode' => env('SMS_DEVELOPMENT_MODE', true),
    
    // Фиксированный код для разработки
    'development_code' => env('SMS_DEVELOPMENT_CODE', '1234'),
    
    // Время жизни кода подтверждения в минутах
    'code_expires_minutes' => env('SMS_CODE_EXPIRES_MINUTES', 10),
    
    // Провайдер SMS (twilio, nexmo, local, etc.)
    'provider' => env('SMS_PROVIDER', 'local'),
    
    // Настройки Twilio
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM_NUMBER'),
    ],
    
    // Настройки Nexmo/Vonage
    'nexmo' => [
        'key' => env('NEXMO_KEY'),
        'secret' => env('NEXMO_SECRET'),
        'from' => env('NEXMO_FROM'),
    ],
    
    // Шаблон SMS сообщения
    'message_template' => 'Ваш код подтверждения для Sheykh Lavka: %s',
    
];
