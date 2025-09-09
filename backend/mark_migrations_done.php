<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->boot();

use Illuminate\Support\Facades\DB;

try {
    DB::table('migrations')->insert([
        ['migration' => '2025_09_07_172328_create_couriers_table', 'batch' => 13],
        ['migration' => '2025_09_07_172349_add_courier_fields_to_orders_table', 'batch' => 13]
    ]);
    echo "Миграции помечены как выполненные\n";
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
