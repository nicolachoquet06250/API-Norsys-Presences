<?php

namespace DI\routes\api;

use PDO;
use Exception;
use DI\router\Context;
use DI\wrappers\Mysql;
use DI\decorators\Json;
use DI\decorators\Route;
use DI\decorators\Title;

#[Route('/api/users')]
#[Title('gestion des utilisateurs')]
class Users {
	public function __construct(
		private Mysql $db
	) {}

	#[Json]
	#[Route('/api/user/register', method: 'post')]
	#[Title("enregistrement d'un utilisateur")]
	public function register(Context $context) {
		$body = $context->body();
		
		if (empty($body['firstname']) || empty($body['lastname']) || empty($body['password']) || empty($body['agency'])) {
			throw new Exception('Veuillez remplire les champs');
		}

		$lastname = str_replace(' ', '', $body['lastname']);
		$pseudo = strtolower(substr($body['firstname'], 0, 1).$lastname);
		
		if (strstr($body['firstname'], '-')) {
			$pseudo = substr(explode('-', $body['firstname'])[0], 0, 1).substr(explode('-', $body['firstname'])[1], 0, 1).$lastname;
		}
		
		if (strstr($body['firstname'], ' ')) {
			$pseudo = substr(explode(' ', $body['firstname'])[0], 0, 1).substr(explode(' ', $body['firstname'])[1], 0, 1).$lastname;
		}
		
		$body['email'] = strtolower($pseudo).'@norsys.fr';
		$body['password'] = sha1($body['password']);
		
		$request = $this->db->prepare('SELECT id FROM `users` WHERE email=:email', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute(['email' => $body['email']]);
		$arr = $request->fetchAll(PDO::FETCH_ASSOC);
		
		if (!empty($arr)) {
			throw new Exception('Un compte avec cet email existe déjà');
		}
		
		$request = $this->db->prepare('INSERT INTO `users` (`firstname`, `lastname`, `email`, `password`, `agency_id`) VALUES(:firstname, :lastname, :email, :password, :agency)', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute($body);

		return $body;	
	}

	#[Json]
	#[Route('/api/user/password', methods: ['post', 'put'])]
	#[Title('changement du mot de passe d\'un utilisateur')]
	public function change_password(Context $context) {
		$body = $context->body();

		if (strtolower($_SERVER['REQUEST_METHOD']) === 'put' || (isset($body['method']) && strtolower($body['method']) === 'put')) {
			$user_id = $body['user_id'];
			$password = sha1($body['password']);
		
			$request = $this->db->prepare('SELECT password FROM `users` WHERE id=:user_id', [
				PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
			]);
			$request->execute([
				'user_id' => $user_id
			]);
			
			$result = $request->fetch(PDO::FETCH_ASSOC);
			
			if (is_array($result) && $result['password'] === $password) {
				throw new Exception('Votre nouveau mot de passe ne doit pas être identique à l\'ancien');
			}
			
			$request = $this->db->prepare('UPDATE `users` SET password=:password WHERE id=:user_id', [
				PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
			]);
			$request->execute([
				'password' => $password,
				'user_id' => $user_id
			]);

			return [
				'error' => false  
			];
		} else {
			http_response_code(400);

			return [
				'error' => true,
				'code' => 400,
				'message' => 'BAD REQUEST'
			];
		}
	}
}