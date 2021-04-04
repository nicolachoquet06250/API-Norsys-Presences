<?php

namespace DI\wrappers;

use DI\decorators\Injectable;
use PDO;

#[Injectable]
class Mysql {
	private PDO $pdo;
	public function __construct() {
		$this->pdo = new PDO('mysql:dbname=' . constant('DB_NAME') . ';host=' . constant('DB_HOST') . ';port=' . constant('DB_PORT'), constant('DB_LOGIN'), constant('DB_PASSWORD'));
	}

	public function __call(string $name, $arguments) {
		return $this->pdo->$name(...$arguments);
	}
}