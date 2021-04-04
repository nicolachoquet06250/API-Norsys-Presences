<?php

namespace DI\routes\api;

use PDO;
use Exception;
use DI\router\Context;
use DI\wrappers\Mysql;
use DI\decorators\Json;
use DI\decorators\Route;

class Calendar {
	public function __construct(
		private Context $context,
		private Mysql $db
	) {}

	private function delete_reservation(): array {
		$body = $this->context->body();
		$user_id = $body['user_id'];
		$date = $body['date'];
		
		if (empty($user_id) || empty($date)) {
			throw new Exception('Une erreur est survenue lors de l\'annulation de votre réservation');
		}
		
		$db = $this->db;
		$request = $db->prepare('DELETE FROM `reservations` WHERE `id_user` = :id_user AND DATE_FORMAT(date, \'%Y-%m-%d\') = :date', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute([
			'id_user' => $user_id,
			'date' => explode('-', $date)[0].'-'.(intval(explode('-', $date)[1]) < 10 ? '0' : '').explode('-', $date)[1].'-'.(intval(explode('-', $date)[2]) < 10 ? '0' : '').explode('-', $date)[2]
		]);
		
		return [
			'error' => false,
			'user_id' => intval($user_id),
			'date' => $date
		];
	}

	private function add_reservation(): array {
		$body = $this->context->body();
		$user_id = $body['user_id'];
		$date = $body['date'];
		
		if (empty($user_id) || empty($date)) {
			throw new Exception('Une erreur est survenue lors de l\'enregistrement de votre réservation');
		}
		
		$db = $this->db;
		$request = $db->prepare('INSERT INTO `reservations` (`id_user`, `date`) VALUES(:id_user, :date)', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute([
			'id_user' => $user_id,
			'date' => $date
		]);
		
		return [
			'error' => false,
			'user_id' => intval($user_id),
			'date' => $date
		];
	}

	private function nbDaysInMonth(int|string $month , int|string $year): int {
		$inMonth = $year * 12 + $month; 
		if (($inMonth > 2037 * 12 - 1) || ($inMonth < 1970)) return 0;
		$next_year = floor(($inMonth + 1) / 12); 
		$next_month = $inMonth + 1 - 12 * $next_year; 
		$timing = mktime(0, 0, 1, $next_month, 1, $next_year) - mktime(0, 0, 1, $month, 1, $year);
		return round(($timing / (3600 * 24))); 
	}

	private function isBisextilYear(int|string|null $year = null): bool {
		if (is_null($year)) $year = date('Y');
		return $this->nbDaysInMonth('02', $year) === 29;
	}

	#[Json]
	#[Route('/api/calendar/([0-9]{0,4})/([0-9]{0,2})')]
	public function get_specific_month_calendar(int $year, int $month) {
		$days = $this->nbDaysInMonth($month, $year);
		$previous_month = $month;
		$previous_month_year = $year;
		if (intval($previous_month) === 1) {
			$previous_month = 12;
			$previous_month_year = intval($previous_month_year) - 1;
		}
		$previous_month_days = $this->nbDaysInMonth(intval($previous_month) - 1, $previous_month_year);
		$bisextil = $this->isBisextilYear($year);
		
		$calendar = [];
		
		for ($i = 0; $i < $days; $i++) {
			$day = intval(date('N', strtotime($year.'-'.$month.'-'.($i + 1))));
			
			if (!isset($calendar[date('W', strtotime($year.'-'.$month.'-'.($i + 1)))])) {
				$calendar[date('W', strtotime($year.'-'.$month.'-'.($i + 1)))] = [];
			}
			$reservations = [];
			$presences = [];
			
			$db = $this->db;
			$request = $db->prepare('SELECT `reservations`.`id_user`, 
											`reservations`.`id` id_reservation, 
											`reservations`.`date` date, 
											`email`, `firstname`, `lastname` 
										FROM `reservations` INNER JOIN `users` 
											ON `reservations`.`id_user` = `users`.`id` 
											WHERE DATE_FORMAT(date, \'%Y-%m-%d\') = :date', 
										[ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
			$request->execute([
				'date' => $year.'-'.($month < 10 ? '0' : '').$month.'-'.($i + 1 < 10 ? '0' : '').($i + 1)
			]);
			$reservations = $request->fetchAll(PDO::FETCH_ASSOC);
		
			$request = $db->prepare('SELECT `presences`.id, 
										DATE_FORMAT(arrival_date, \'%H:%i\') arrival, 
										DATE_FORMAT(departure_date, \'%H:%i\') departure, 
											`firstname`, `lastname`, `email`, `name` 
											FROM `presences` INNER JOIN `users` INNER JOIN `agencies` 
												ON `presences`.`user_id` = `users`.`id` 
												AND `users`.`agency_id` = `agencies`.`id` 
												WHERE DATE_FORMAT(arrival_date, \'%Y-%m-%d\') = :date', 
										[ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
			$request->execute([
				'date' => $year.'-'.($month < 10 ? '0' : '').$month.'-'.($i < 10 ? '0' : '').($i + 1)
			]);
			$presences = $request->fetchAll(PDO::FETCH_ASSOC);
			
			$calendar[date('W', strtotime($year.'-'.$month.'-'.($i + 1)))][] = [
				'day' => $day,
				'date' => $year.'-'.$month.'-'.($i + 1),
				'reservations' => $reservations,
				'presences' => $presences
			];
		}
		
		return [
			'year' => intval($year),
			'month' => intval($month),
			'bisextil' => $bisextil,
			'nb_days' => [
				'previous' => $previous_month_days,
				'current' => $days
			],
			'calendar' => $calendar
		];
	}

	#[Json]
	#[Route('/api/reservation', methods: ['post', 'delete'])]
	public function create_reservation() {
		$body = $this->context->body();

		return isset($body['method']) && strtolower($body['method']) === 'delete' 
			? $this->delete_reservation() : $this->add_reservation();
	}
}