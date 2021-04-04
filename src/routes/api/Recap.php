<?php

namespace DI\routes\api;

use PDO;
use Exception;
use DI\router\Context;
use DI\wrappers\{
	Mysql, Mailer
};
use DI\decorators\{
	Json, Route
};

class Recap {
	public function __construct(
		private Mysql $db
	) {}

	#[Json]
	#[Route('/api/recap/upload', method: 'post')]
	public function upload_image(Context $context) {
		if (is_null($context->upload('image'))) {
			if (!is_dir(__ROOT__.'/app/assets/recaps/images')) {
				mkdir(__ROOT__.'/app/assets/recaps/images', 0777, true);
			}
	
			$path = __ROOT__.'/app/assets/recaps/images/'.basename($_FILES['image']['name']);
			
			$success = false;
			$message = "Il y a eu un problème lors de l'upload";

			if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
				$success = true;
				$message = 'https://'.$_SERVER['SERVER_NAME'].'/assets/recaps/images/'.basename($_FILES['image']['name']);
			}
			return [
				'success' => $success, 
				'message' => $message
			];
		}
	}

	#[Json]
	#[Route('/api/recaps/([^\/]+)')]
	public function get_recaps_from_agency(string $token) {
		if (empty($token)) {
			throw new Exception('Authentification invalide');
		}
		$token = base64_decode($token);
		if ($token) {
			$token = json_decode($token, true);

			if (json_last_error()) {
				throw new Exception(json_last_error_msg());
			}
		}

		$request = $this->db->prepare('SELECT recap.id recap_id, 
										DATE_FORMAT(creation_date, \'%Y-%m-%d\') creation_date, 
											content, object, 
											users.firstname, users.lastname, users.email 
												FROM recap INNER JOIN users 
													ON recap.user_id = users.id 
													WHERE recap.agency_id = (
														SELECT id FROM agencies WHERE name = :agency
													)', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
		$request->execute([ 'agency' => $token['agency'] ]);
		$result = $request->fetchAll(PDO::FETCH_ASSOC);

		return $result;
	}

	private function get_week_extremity_days(int $week_number, int $year): array {
		return [
			'first_day' => date("Y-m-d", strtotime('First Monday January ' . $year . ' +' . ($week_number - 1) . ' Week')),
			'last_day' => date("Y-m-d", strtotime('First Monday January ' . $year . ' +' . $week_number . ' Week -3 day'))
		];
	}

	#[Json]
	#[Route('/api/recap/([0-9]+)/([^\/]+)')]
	public function get_recap_from_id(int $id, string $token) {
		$monthes = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'septembre', 'Octobre', 'Novembre', 'Décembre'];

		if (empty($token)) {
			throw new Exception('Authentification invalide');
		}
		$token = base64_decode($token);
		if ($token) {
			$token = json_decode($token, true);

			if (json_last_error()) {
				throw new Exception(json_last_error_msg());
			}
		}

		$request = $this->db->prepare('SELECT recap.id recap_id, 
										DATE_FORMAT(creation_date, \'%Y-%m-%d\') creation_date, 
											content, object, 
											users.firstname, users.lastname, users.email 
												FROM recap INNER JOIN users 
													ON recap.user_id = users.id 
													WHERE recap.agency_id = (
														SELECT id FROM agencies WHERE name = :agency
													) AND recap.id = :id', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
		$request->execute([
			'agency' => $token['agency'],
			'id' => $id
		]);
		$result = $request->fetchAll(PDO::FETCH_ASSOC);
		if (empty($result) === false) $result = $result[0];

		$week = $this->get_week_extremity_days(date('W', strtotime($result['creation_date'])), date('Y', strtotime($result['creation_date'])));

		$start = date('d', strtotime($week['first_day']));
		$end = date('d', strtotime($week['last_day']));
		$month = $monthes[intval(date('n', strtotime($week['first_day'])))];

		$result['vars'] = [
			'start' => $start,
			'end' => $end,
			'month' => $month
		];

		return $result;
	}

	#[Json]
	#[Route('/api/recap', method: 'post')]
	public function send_recap(Context $context, Mailer $mailer) {
		$monthes = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'septembre', 'Octobre', 'Novembre', 'Décembre'];

		$body = $context->body();

		if (empty($body['html']) === false) {
			$token = $body['token'];
			$user = json_decode(base64_decode($token), true);

			$email = $user['email'];

			$agency = $user['agency'];

			$week = $this->get_week_extremity_days(date('W'), date('Y'));

			$start = date('d', strtotime($week['first_day']));
			$end = date('d', strtotime($week['last_day']));
			$month = $monthes[intval(date('n', strtotime($week['first_day'])))];

			$object = 'Récap Hebdo Sophia ' . date('d/m/Y');

			$agency_users = [];

			$request = $this->db->prepare('SELECT mailing_list FROM `agencies` WHERE name = :agency', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
			$request->execute([ 'agency' => $agency ]);
			$agency_users = $request->fetch(PDO::FETCH_ASSOC);

			if (!empty($agency_users) && $agency_users['mailing_list'] === null) {
				$request = $this->db->prepare('SELECT * FROM `users` WHERE agency_id = ( SELECT id FROM agencies WHERE name = :agency )', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
				$request->execute([ 'agency' => $agency ]);
				$agency_users = $request->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$agency_users = [
					[
						'email' => $agency_users['mailing_list']
					]
				];
			}
			
			// DEBUG
			/*$agency_users = [
				[
					'email' => $email
				]
			];*/

			$request = $this->db->prepare('INSERT INTO recap (`user_id`, `agency_id`, `object`, `content`) VALUES(:user_id, (SELECT id FROM agencies WHERE name = :agency), :object, :content)', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
			$request->execute([
				'user_id' => $user['id'],
				'agency' => $user['agency'],
				'object' => $object,
				'content' => $body['html']
			]);

			$htmlBody = str_replace(['%start%', '%end%', '%month%'], [$start, $end, $month], $body['html']);
			$html = <<<HTML
				<!DOCTYPE HTML PUBLIC "~//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<title>Norsys Sophia | Fiche de présence</title>
						<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" 
							rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" 
							crossorigin="anonymous">
						<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.23.0/ui/trumbowyg.min.css" />
						<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.19.1/plugins/emoji/ui/trumbowyg.emoji.css" />
					</head>
					<body>{$htmlBody}</body>
				</html>
			HTML;

			$mails = [];

			$agency_users = array_map(fn($c) => $c['email'], $agency_users);

			$mail = $mailer->send($agency_users, $html, $object);

			if ($mail !== true) {
				throw new Exception($mail);
			}

			return [
				'error' => false
			];
		} else {
			throw new Exception('Vous devez remplire le message avant de l\'envoyer...');
		}
	}

	#[Json]
	#[Route('/api/recap/save-template', methods: ['post', 'put'])]
	public function save_recap_template(Context $context) {
		$body = $context->body();

		if (empty($body['html']) === false) {
			file_put_contents(__ROOT__.'/app/recap_templates/hebdo.html', $body['html']);
		}

		return [
			'error' => false
		];
	}
}