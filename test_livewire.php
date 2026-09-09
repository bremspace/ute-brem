<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$finder = app('livewire.finder');
$ref = new ReflectionProperty($finder, 'classLocations');
$ref->setAccessible(true);
$locations = $ref->getValue($finder);

echo "Class Locations: " . print_r($locations, true) . "\n";

$ref2 = new ReflectionProperty($finder, 'classNamespaces');
$ref2->setAccessible(true);
$namespaces = $ref2->getValue($finder);

echo "Class Namespaces: " . print_r($namespaces, true) . "\n";

// Try to resolve component
try {
    $resolved = app('livewire.factory')->resolveComponentNameAndClass('product-list');
    echo "Resolved: " . print_r($resolved, true) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
