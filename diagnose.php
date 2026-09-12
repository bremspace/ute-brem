<?php
// TEMPORARY diagnostic — run: php diagnose.php  (then delete this file)
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== PHP ===\n";
echo "version: " . PHP_VERSION . "\n";
echo "opcache.enable_cli: " . ini_get('opcache.enable_cli') . "\n";
echo "ionCube loaded: " . (extension_loaded('ionCube Loader') || in_array('ionCube Loader', array_map('trim', explode(',', php_sapi_name()))) ? 'maybe' : 'check below') . "\n";
foreach (['ionCube Loader','SourceGuardian','pdo_mysql','mbstring','openssl','ctype','json','tokenizer','xml','curl','fileinfo','bcmath','dom','filter','hash','session'] as $ext) {
    echo str_pad($ext, 18) . ': ' . (extension_loaded($ext) ? 'YES' : 'NO') . "\n";
}

echo "\n=== AUTOLOAD ===\n";
$autoload = __DIR__ . '/vendor/autoload.php';
echo "autoload exists: " . (file_exists($autoload) ? 'YES' : 'NO') . "\n";
require $autoload;
echo "Application class: " . (class_exists('Illuminate\Foundation\Application') ? 'YES' : 'NO') . "\n";
echo "ApplicationBuilder class: " . (class_exists('Illuminate\Foundation\Configuration\ApplicationBuilder') ? 'YES' : 'NO') . "\n";
echo "Framework VERSION: " . (defined('Illuminate\Foundation\Application::VERSION') ? Illuminate\Foundation\Application::VERSION : 'undef') . "\n";

echo "\n=== APPLICATION METHODS ===\n";
foreach (['configure','withRouting','withMiddleware','withExceptions','create','make'] as $m) {
    echo str_pad($m, 16) . ': ' . (method_exists('Illuminate\Foundation\Application', $m) ? 'YES' : 'NO') . "\n";
}
echo "ApplicationBuilder::create: " . (method_exists('Illuminate\Foundation\Configuration\ApplicationBuilder', 'create') ? 'YES' : 'NO') . "\n";

echo "\n=== BOOTSTRAP ===\n";
try {
    $app = require __DIR__ . '/bootstrap/app.php';
    echo "bootstrap/app.php returned: " . (is_object($app) ? get_class($app) : gettype($app)) . "\n";
    if (is_object($app)) {
        echo "app->make('config'): " . (is_object($app->make('config')) ? get_class($app->make('config')) : 'FAIL') . "\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . get_class($e) . "\n";
    echo "MSG: " . $e->getMessage() . "\n";
    echo "AT: " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== .env ===\n";
echo ".env exists: " . (file_exists(__DIR__ . '/.env') ? 'YES' : 'NO') . "\n";
echo ".env writable: " . (is_writable(__DIR__ . '/.env') ? 'YES' : 'NO') . "\n";

echo "\n=== DONE ===\n";
