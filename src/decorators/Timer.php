<?php

namespace DI\decorators;

use Attribute;
use DI\bases\AttributeBase;
use DI\helpers\TimerWrapperClasses;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD|Attribute::TARGET_FUNCTION)]
class Timer extends AttributeBase {
	private bool $enabled = false;
	
	public function __construct() {
		if (defined('DEBUG') && constant('DEBUG') === true) {
			$this->enabled = true;
		}
	}

	public function manage(): void {
		TimerWrapperClasses::addElement($this->target, $this->isMethod() ? $this->methodName : '__construct');
	}
}