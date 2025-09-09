<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Проверим, есть ли уже запись в таблице migrations
    $migrationName = '2025_09_06_142304_create_banners_table';
    $exists = DB::table('migrations')->where('migration', $migrationName)->exists();
    
    if (!$exists) {
        // Добавляем запись о том, что миграция выполнена
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => DB::table('migrations')->max('batch') + 1
        ]);
        echo "Миграция {$migrationName} отмечена как выполненная\n";
    } else {
        echo "Миграция {$migrationName} уже отмечена как выполненная\n";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
