<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

echo "Сборщики в базе данных:\n";
$pickers = DB::table('pickers')->select('id', 'name', 'login')->get();

foreach ($pickers as $picker) {
    echo "ID: {$picker->id}, Name: {$picker->name}, Login: {$picker->login}\n";
}

echo "\nЗаказы с назначенными сборщиками:\n";
$orders = DB::table('orders')->whereNotNull('picker_id')->select('id', 'picker_id', 'status')->get();

foreach ($orders as $order) {
    echo "Order ID: {$order->id}, Picker ID: {$order->picker_id}, Status: {$order->status}\n";
}
