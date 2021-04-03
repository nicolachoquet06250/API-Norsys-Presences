<?php

namespace DI;

use DI\decorators\Injectable;

#[Injectable(\DI\interfaces\Test::class)]
class Test implements \DI\interfaces\Test {
    public function myFunc(Application $app) {
        return $app->test();
    }
}
