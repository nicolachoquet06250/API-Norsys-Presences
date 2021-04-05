<?php

namespace DI\routes\api;

use PDO;
use DateTime;
use Exception;
use DI\router\Context;
use DI\wrappers\Mysql;
use DI\decorators\{
	Json, Route,
    Timer
};

class Presences {
	public function __construct(
		private Mysql $db
	) {}

	#[Timer] //#[Json]
	#[Route('/api/presences')]
	public function get_presences() {
		try {
			$token = $_GET['token'];
			$user = base64_decode($token);
			$user = json_decode($user, true);
		}
		catch(Exception $e) {
			http_response_code(500);

			exit(json_encode([
				'error' => true,
				'authent' => true,
				'message' => $e->getMessage()
			]));
		}
		
		$request = $this->db->query('SELECT firstname, lastname, email, arrival_date, departure_date FROM `presences` INNER JOIN `users` ON `presences`.user_id = `users`.id');
		$arr = $request->fetchAll(PDO::FETCH_ASSOC);
		return $arr;
	}

	#[Timer] #[Json]
	#[Route('/api/presences/today')]
	public function get_presences_for_today() {
		try {
			if (empty($_GET['token'])) {
			  	throw new Exception('Identification invalide');
			}
			$token = $_GET['token'];
			$user = base64_decode($token);
			
			if ($user === false) {
			  	throw new Exception('Token invalide');
			}
			
			$user = json_decode($user, true);
			
			if (empty(json_last_error_msg())) {
			  	throw new Exception('Identification invalide');
			}
			
			if (empty($user['firstname']) || empty($user['lastname']) || empty($user['email']) || empty($user['id'])) {
			  	throw new Exception('Identification invalide');
			} 
		}
		catch(Exception $e) {
			exit(json_encode([
				'error' => true,
				'authent' => true,
				'message' => $e->getMessage()
			]));
		}
		
		$request = $this->db->query('SELECT firstname, lastname, email, arrival_date, departure_date FROM `presences` INNER JOIN `users` ON `presences`.user_id = `users`.id');
		$arr = $request->fetchAll(PDO::FETCH_ASSOC);
		
		$arr = array_map(function($a) {
			if (!empty($a['departure_date'])) {
				$a['departure_date'] = DateTime::createFromFormat('Y-m-d H:i:s', $a['departure_date']);
			}
			$a['arrival_date'] = DateTime::createFromFormat('Y-m-d H:i:s', $a['arrival_date']);
			
			return $a;
		}, $arr);
		
		$today = (new DateTime())->format('Y-m-d');
		
		$arr = array_reduce($arr, function ($rediucer, $curr) use($today) {
			if ($curr['arrival_date']->format('Y-m-d') === $today) {
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

	#[Timer] #[Json]
	#[Route('/api/presence', method: 'post')]
	public function add_presence(Context $context) {
		$body = $context->body();

		switch ($body['type']) {
			case 'arrival':
				$user_id = $body['user_id'];
				$request = $this->db->prepare('INSERT INTO `presences` (`user_id`, `arrival_date`) VALUES(:user_id, :arrival_date)', [
					PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
				]);
				
				$request->execute([
					'user_id' => $user_id,
					'arrival_date' => (new DateTime())->format('Y-m-d H:i:s')
				]);
				
				return [
					'error' => false  
				];
				
			case 'departure':
				$user_id = $body['user_id'];
				$request = $this->db->prepare('SELECT id FROM `presences` WHERE user_id=:user_id ORDER BY id DESC LIMIT 1', [
					PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
				]);
				$request->execute([
					'user_id' => $user_id
				]);
				$arr = $request->fetchAll(PDO::FETCH_ASSOC);
				if (empty($arr)) {
					throw new Exception('Vous devez d\'abord définir une heure d\'arrivée');
				}
				$id = $arr[0]['id'];
				$request = $this->db->prepare('UPDATE `presences` SET `departure_date`=:departure_date WHERE id=:id', [
					PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
				]);
				$request->execute([
					'departure_date' => (new DateTime())->format('Y-m-d H:i:s'),
					'id' => $id
				]);
				
				return [
					'error' => false  
				];
				
			default:
				throw new Exception('invalide presence type');
		}
	}
}