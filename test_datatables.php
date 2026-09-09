<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing DataTables endpoint...\n";
echo "Products count: " . \App\Models\Product::count() . "\n";
echo "DataTables class exists: " . (class_exists('Yajra\DataTables\Facades\DataTables') ? 'YES' : 'NO') . "\n";

// Try to create a datatables response
try {
    $products = \App\Models\Product::query()->select(['id','name','selling_price','stock_global','is_active','is_published'])->limit(3)->get();
    $response = datatables()->of($products)->make(true);
    echo "DataTables response status: " . $response->getStatusCode() . "\n";
    echo substr($response->getContent(), 0, 200) . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
