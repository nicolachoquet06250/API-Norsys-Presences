<?php

namespace DI\helpers;

use DI\injection\InjectionContainer;

class Timer {
	public static function isEnabled() {
		return defined('DEBUG') && constant('DEBUG') === true 
			&& defined('TIME_ENABLED') && constant('TIME_ENABLED') === true;
	}

	public static function create(mixed $object, string $method, ...$attrs) {
		$ic = new InjectionContainer();
		if ($method === '__construct') {
			if (TimerWrapperClasses::isTimingMethod($object, $method)) {
				$twc = new TimerWrapperClasses($object, $method);
				return $twc->run(...$attrs);
			} else {
				return $ic->injectInConstruct($object, ...$attrs);
			}
		} else {
			if (TimerWrapperClasses::isTimingMethod('\\' . get_class($object), $method)) {
				$twc = new TimerWrapperClasses($object, $method);
				return $twc->run(...$attrs);
			} else {
				return $ic->injectIntoMethod($object, $method, ...$attrs);
			}
		}
	}
}