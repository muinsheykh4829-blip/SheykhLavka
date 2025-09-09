<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Обновление SMS кодов на 4-значные...\n";

// Обновляем все активные коды на 1234
$updated = App\Models\User::whereNotNull('verification_code')
    ->update(['verification_code' => '1234']);

echo "Обновлено пользователей: {$updated}\n";

// Покажем результат
echo "\nПользователи с обновленными SMS кодами:\n";
$users = App\Models\User::whereNotNull('verification_code')->get(['id', 'phone', 'verification_code']);
foreach($users as $user) {
    echo "ID: {$user->id}, Phone: {$user->phone}, Code: {$user->verification_code}\n";
}
