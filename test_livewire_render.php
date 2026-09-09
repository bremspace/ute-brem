<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test: Render Livewire component
try {
    $html = \Livewire\Livewire::render('product-list');
    echo "Livewire render length: " . strlen($html) . " bytes\n";
    echo "First 500 chars:\n";
    echo substr($html, 0, 500) . "\n";
} catch (\Throwable $e) {
    echo "Error rendering: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
