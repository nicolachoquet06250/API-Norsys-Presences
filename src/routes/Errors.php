<?php

namespace DI\routes;

use DI\decorators\{
	MethodNotAllowed, 
	PageNotFound,
    Timer
};

class Errors {

	#[Timer]
	#[PageNotFound]
	public function error404(): string {
		return 'PAGE NOT FOUND';
	}

	#[Timer]
	#[MethodNotAllowed]
	public function methodNotAllowed(): string {
		return 'METHOD NOT ALLOWED';
	}
}