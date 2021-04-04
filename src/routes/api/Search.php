<?php

namespace DI\routes\api;

use PDO;
use DateTime;
use DI\decorators\Json;
use DI\wrappers\Mysql;
use DI\decorators\Route;

class Search {

	#[Json]
	#[Route('/api/search/history')]
	public function get_full_history(Mysql $db) {
		$request = $db->query('SELECT DATE_FORMAT(arrival_date, \'%Y-%m-%d\') arrival_date FROM `presences` GROUP BY DATE_FORMAT(arrival_date, \'%Y-%m-%d\') ORDER BY arrival_date DESC ');
		
		$results = $request->fetchAll(PDO::FETCH_ASSOC);
		$results = array_map(fn($e) => $e['arrival_date'], $results);
		
		return $results;
	}

	#[Json]
	#[Route('/api/search/history/([0-9]{0,4}\-[0-9]{0,2}\-[0-9]{0,2})')]
	public function get_history_per_date(Mysql $db, string $date) {
		$request = $db->query('SELECT firstname, lastname, email, arrival_date, departure_date FROM `presences` INNER JOIN `users` ON `presences`.user_id = `users`.id');
		$arr = $request->fetchAll(PDO::FETCH_ASSOC);
		
		$arr = array_map(function($a) {
			if (!empty($a['departure_date'])) {
				$a['departure_date'] = DateTime::createFromFormat('Y-m-d H:i:s', $a['departure_date']);
			}
			$a['arrival_date'] = DateTime::createFromFormat('Y-m-d H:i:s', $a['arrival_date']);
			
			return $a;
		}, $arr);
		
		$arr = array_reduce($arr, function ($rediucer, $curr) use($date) {
			if ($curr['arrival_date']->format('Y-m-d') === $date) {
				$rediucer[] = $curr;
			}
			return $rediucer;
		}, []);
		
		$arr = array_map(function($a) {
			if (!empty($a['departure_date'])) {
				$a['departure_date'] = $a['departure_date']->format('Y-m-d H:i:s');
			}
			$a['arrival_date'] = $a['arrival_date']->format('Y-m-d H:i:s');
			
			return $a;
		}, $arr);
		
		return $arr;
	}
}