<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Courier;

echo "=== ВСЕ КУРЬЕРЫ В СИСТЕМЕ ===\n";
$couriers = Courier::all();
foreach($couriers as $c) {
    echo "ID {$c->id}: {$c->first_name} {$c->last_name} ({$c->username})\n";
}
