<?php

namespace DI\routes;

use DI\decorators\MethodNotAllowed;
use DI\decorators\PageNotFound;

class Errors {

	#[PageNotFound]
	public function error404(): string {
		return 'PAGE NOT FOUND';
	}

	#[MethodNotAllowed]
	public function methodNotAllowed(): string {
		return 'METHOD NOT ALLOWED';
	}
}