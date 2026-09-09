<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test 1: Check if Livewire component can be found
try {
    $component = \Livewire\Livewire::new('product-list');
    echo "Component created: " . get_class($component) . "\n";
} catch (\Throwable $e) {
    echo "Error creating component: " . $e->getMessage() . "\n";
}

// Test 2: Check Livewire config
echo "Config class_namespace: " . config('livewire.class_namespace') . "\n";
echo "Config view_path: " . (config('livewire.view_path') ?? 'NOT SET') . "\n";
echo "Config layout: " . (config('livewire.layout') ?? 'NOT SET') . "\n";

// Test 3: Check if component exists
try {
    $exists = \Livewire\Livewire::exists('product-list');
    echo "Component exists: " . ($exists ? 'YES' : 'NO') . "\n";
} catch (\Throwable $e) {
    echo "Error checking existence: " . $e->getMessage() . "\n";
}
