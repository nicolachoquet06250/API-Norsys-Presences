<?php

namespace DI\routes;

use DI\decorators\Route;

class Front {
	#[Route('/templates/hebdo.html')]
	public function hebdo_tpl() {
		header('Access-Control-Allow-Origin: *');
		$content = file_get_contents(__ROOT__.'/app/recap_templates/hebdo.html');
		echo $content;
	}
}