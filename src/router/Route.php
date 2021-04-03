<?php

namespace DI\router;

class Route {

	private static array $routes = [];

	/**
	 * Function used to add a new route
	 * 
	 * @param string $expression    Route string or expression
	 * @param callable $function    Function to call if route with allowed method is found
	 * @param string|string[] $method  Either a string of allowed method or an array with string values
	 **/
	public static function add(string $expression, callable $function, string|array $method = 'get'): void {
		static::$routes[$expression] = [
			'expression' => $expression,
			'function' => $function,
			'method' => $method
		];
	}

	public static function getAll(): array {
		return array_values(static::$routes);
	}

	public static function pathNotFound(callable $function): void {
		\Steampixel\Route::pathNotFound($function);
	}

	public static function methodNotAllowed(callable $function) {
		\Steampixel\Route::methodNotAllowed($function);
	}

	public static function run($basepath = '', $case_matters = false, $trailing_slash_matters = false, $multimatch = false) {
		foreach(static::$routes as $route) {
			\Steampixel\Route::add($route['expression'], $route['function'], $route['method']);
		}

		\Steampixel\Route::run($basepath, $case_matters, $trailing_slash_matters, $multimatch);
	}

}