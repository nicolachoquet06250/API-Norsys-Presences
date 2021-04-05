<?php

namespace DI\router;

use DI\decorators\Injectable;
use DI\decorators\Timer;

#[Injectable]
#[Timer]
class Context {
	private string $body;
	private array $queryString;
	private array $uploads;
	private array $sessions;

	public function __construct() {
		if (session_status() === 1) {
			session_start();
		}
		$this->body = file_get_contents('php://input');
		$this->queryString = $_GET;
		$this->uploads = $_FILES;
		$this->sessions = $_SESSION;
	}

	#[Timer]
	public function body(): array {
		return json_decode($this->body, true);
	}
	
	#[Timer]
	public function queryString(?string $key = null): string|array {
		return is_null($key) ? $this->queryString : $this->queryString[$key];
	}

	#[Timer]
	public function upload(string $key): array {
		return empty($this->uploads[$key]) ? null : $this->uploads[$key];
	}

	public function session(?string $key = null, mixed $value = null, bool $reset = false): string|null|array|self {
		if ($reset === true) {
			$this->sessions = [];
			$_SESSION = [];
		}

		if (is_null($key) && is_null($value)) {
			return $this->sessions;
		}
		if (!is_null($key) && is_null($value)) {
			return $this->sessions[$key] ?? null;
		}
		$this->sessions[$key] = $value;
		$_SESSION[$key] = $value;
		return $this;
	}
}