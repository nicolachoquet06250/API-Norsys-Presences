<?php

namespace DI;

use DI\decorators\Injectable;
use DI\router\Router;
use Steampixel\Route;

#[Injectable]
class Application {
    private string $test = '';

    public function __construct() {}

    public function setTest(string $test) {
        $this->test = $test;
    }

    public function test() {
        return 'test' . ' ' . $this->test;
    }
    
    public function run(string $basePath = '/'): void {
        Router::analyse();

        Route::run($basePath);
    }
}
