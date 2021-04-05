<?php

namespace DI\helpers;

use DateTime;
use ReflectionClass;
use ReflectionMethod;
use DI\router\Context;
use DI\decorators\Timer;
use DI\injection\InjectionContainer;

class TimerWrapperClasses {
	private static array $timings = [];
	private static array $elements = [];

	public function __construct(
		private string|object $target, 
		private string $method
	) {}

	public function setCurrentTiming(Context $context, mixed $start, mixed $end) {
		if (\DI\helpers\Timer::isEnabled()) {
			$target = is_object($this->target) ? '\\' . get_class($this->target) : $this->target;
			if (!isset(static::$timings[$target])) {
				static::$timings[$target] = [];
			}
			
			static::$timings[$target][$this->method][] = [
				'start' => $start,
				'end' => $end
			];
			$timings = array_merge((is_null($context->session('debug_time')) ? [] : json_decode($context->session('debug_time'), true)), static::$timings);
			$context->session('debug_time', json_encode($timings));
			/*$context->session($target . '_' . $this->method, json_encode([
				'start' => $start,
				'end' => $end
			]));*/
		}
	}

	public static function addElement(string $target, string $method) {
		if (\DI\helpers\Timer::isEnabled()) {
			if (!isset(static::$elements[$target])) {
				static::$elements[$target] = [];
			}
			static::$elements[$target][] = $method;
		}
	}

	public function run(...$args): mixed {
		$ic = new InjectionContainer();
		if ($this->method === '__construct') {
			$start = microtime(true);//(new DateTime())->getTimestamp();
			$r = $ic->injectInConstruct($this->target, ...$args);
			$end = microtime(true);//(new DateTime())->getTimestamp();

			$ic->injectIntoMethod($this, 'setCurrentTiming', $start, $end);

			return $r;
		}

		$start = microtime(true);//(new DateTime())->getTimestamp();
		$r = $ic->injectIntoMethod($this->target, $this->method, ...$args);
		$end = microtime(true);//(new DateTime())->getTimestamp();

		$ic->injectIntoMethod($this, 'setCurrentTiming', $start, $end);
		
		return $r;
	}

	public static function analyze() {
		DirAnalyser::analyse(__ROOT__.'/src', function($root, $elem) {
			$class = str_replace(
				[ __ROOT__, 'src', '/', '.php' ], 
				[ '', 'DI', '\\', '' ], 
				$root.'/'.$elem
			);
			$rc = new ReflectionClass($class);
			if (!empty($rc->getAttributes(Timer::class))) {
				foreach ($rc->getAttributes(Timer::class) as $attribute) {
					Attribute::manage($attribute, $class);
				}
			}

			foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $rm) {
				if (!empty($rm->getAttributes(Timer::class))) {
					foreach ($rm->getAttributes(Timer::class) as $attribute) {
						Attribute::manage($attribute, $class, $rm->getName());
					}
				}
			}
		});
	}

	public static function elements() {
		return static::$elements;
	}

	public static function timings() {
		return static::$timings;
	}

	public static function isTimingMethod(string $target, string $method) {
		if (\DI\helpers\Timer::isEnabled()) {
			$targetIsPossiblyTiming = isset(static::$elements[$target]);
			if (!$targetIsPossiblyTiming) return false;

			return in_array($method, static::$elements[$target]);
		} else {
			return false;
		}
	}
}