<?php

namespace DI\routes\api;

use PDO;
use Exception;
use DI\wrappers\Mysql;
use DI\decorators\Json;
use DI\decorators\Route;

class Agencies {
	#[Json]
	#[Route('/api/agencies')]
	public function get_all_agencies(Mysql $db) {
		$request = $db->query('SELECT * FROM `agencies`');
		$results = $request->fetchAll(PDO::FETCH_ASSOC);
		
		return $results;
	}
}