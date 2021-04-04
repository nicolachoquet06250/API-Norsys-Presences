<?php

namespace DI;

use DI\router\{
    Router, Route
};

class Application {
    public function __construct() {}
    
    public function run(string $basePath = '/'): void {
        Router::analyse();

        Route::run($basePath);
    }
}
