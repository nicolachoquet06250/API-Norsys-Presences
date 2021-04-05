<?php

namespace DI;

use DI\decorators\Timer;
use DI\router\{
    Router, Route
};

class Application {
    #[Timer]
    public function run(string $basePath = '/'): void {
        Router::analyse();

        Route::run($basePath);
    }
}
