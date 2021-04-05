<?php

namespace DI\routes;

use DI\decorators\{
	Json, Route, Timer
};
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