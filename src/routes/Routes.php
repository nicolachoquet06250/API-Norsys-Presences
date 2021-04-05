<?php

namespace DI\routes;

use DI\decorators\Json;
use DI\decorators\Route;
use DI\decorators\Timer;
use DI\router\Route as Router;

#[Timer] #[Json]
#[Route('/routes')]
class Routes {
	#[Timer] 
	public function get() {
		$routes = array_map(fn($a) => ['expression' => $a['expression'], 'method' => $a['method']], Router::getAll());
		return $routes;
	}
}