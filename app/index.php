<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

use \DI\Application;
use \DI\injection\DI;
use DI\enums\ViewEngines;

require __DIR__.'/../vendor/autoload.php';
define('__ROOT__', realpath(__DIR__ . '/..'));
define('VIEW_ENGINE', ViewEngines::BLADE);
define('VIEW_DIR', __ROOT__.'/app/views');
define('VIEW_CACHE_DIR', __ROOT__.'/app/views/cache');

DI::analyze(__ROOT__ . '/src');

try {
    $app = new Application();
    $app->run();
} catch (Exception $e) {
    http_response_code(500);
    
    echo '<pre>' . json_encode([
        'error' => 500,
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'trace' => $e->getTrace()
    ], JSON_PRETTY_PRINT) . '</pre>';
}
