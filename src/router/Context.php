<?php

namespace DI\router;

use DI\decorators\Injectable;

#[Injectable]
class Context {
	private string $body;
	private array $queryString;
	private array $uploads;

	public function __construct() {
		$this->body = file_get_contents('php://input');
		$this->queryString = $_GET;
		$this->uploads = $_FILES;
	}

	public function body(): array {
		return json_decode($this->body, true);
	}
	
	public function queryString(?string $key = null): string|array {
		return is_null($key) ? $this->queryString : $this->queryString[$key];
	}

	public function upload(string $key): array {
		return empty($this->uploads[$key]) ? null : $this->uploads[$key];
	}
}