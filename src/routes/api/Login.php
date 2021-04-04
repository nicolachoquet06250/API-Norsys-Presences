<?php

namespace DI\routes\api;

use PDO;
use Exception;
use DI\router\Context;
use DI\wrappers\Mysql;
use DI\decorators\{
	Json, Route
};

class Login {
	#[Json]
	#[Route('/api/user/login', method: 'post')]
	public function login(Context $context, Mysql $db) {
		$body = $context->body();
		$body['password'] = sha1($body['password']);
		
		$request = $db->prepare('SELECT `users`.id id, firstname, lastname, email, `agencies`.name agency FROM `users` INNER JOIN `agencies` ON `users`.agency_id = `agencies`.id WHERE email=:email AND password=:password', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute($body);
		$arr = $request->fetchAll(PDO::FETCH_ASSOC);
		
		if (!empty($arr)) {
			$result = $arr[0];
		} else {
			throw new Exception('Aucun utilisateur ne correspond à ces identifiants');
		}
		
		$result['token'] = base64_encode((json_encode($result)));
		
		return $result;
	}	
}