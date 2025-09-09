<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Пользователи с SMS кодами:\n";
$users = App\Models\User::whereNotNull('verification_code')->get(['id', 'phone', 'verification_code', 'verification_code_expires_at']);

if ($users->count() > 0) {
    foreach($users as $user) {
        echo "ID: {$user->id}, Phone: {$user->phone}, Code: {$user->verification_code}, Expires: {$user->verification_code_expires_at}\n";
    }
} else {
    echo "Нет пользователей с активными SMS кодами\n";
    
    // Покажем всех пользователей
    echo "\nВсе пользователи:\n";
    $allUsers = App\Models\User::all(['id', 'phone', 'verification_code']);
    foreach($allUsers as $user) {
        echo "ID: {$user->id}, Phone: {$user->phone}, Code: " . ($user->verification_code ?: 'нет') . "\n";
    }
}
