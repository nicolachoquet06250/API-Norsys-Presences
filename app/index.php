<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

use DI\Application;
use DI\injection\DI;
use DI\helpers\ConstCreator;

require __DIR__.'/../vendor/autoload.php';
define('__ROOT__', realpath(__DIR__ . '/..'));

try {
    ConstCreator::create(
        __ROOT__ . '/env.json', 
        required: [
            "VIEW_ENGINE", "VIEW_DIR", "VIEW_CACHE_DIR",
            "DB_NAME", "DB_HOST", "DB_PORT", "DB_LOGIN", "DB_PASSWORD"
        ]
    );

    try {
        DI::analyze(__ROOT__ . '/src');

        $app = new Application();
        $app->run();
    } catch (Exception $e) {
        http_response_code(500);
        
        if (constant('DEBUG')) {
            dd([
                'error' => true,
                'code' => 500,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTrace()
            ]);
        }
    }
} catch(Exception $e) {
    echo '<pre>' . json_encode([
        'error' => true,
        'code' => 500,
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT) . '</pre>';
}