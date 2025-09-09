<?php
require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$order = \App\Models\Order::find(3);
if($order) {
    $order->update([
        'status' => 'accepted', 
        'picker_id' => null
    ]);
    echo "Заказ {$order->order_number} обновлен до статуса accepted без сборщика\n";
} else {
    echo "Заказ не найден\n";
}
