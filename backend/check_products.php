<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking products table structure...\n";
$product = App\Models\Product::first();
if($product) {
    echo "Product attributes:\n";
    print_r($product->getAttributes());
} else {
    echo "No products found\n";
}
