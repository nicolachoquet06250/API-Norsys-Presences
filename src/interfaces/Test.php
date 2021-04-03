<?php

namespace DI\interfaces;

use DI\Application;

interface Test {
	public function myFunc(Application $app): string;
}