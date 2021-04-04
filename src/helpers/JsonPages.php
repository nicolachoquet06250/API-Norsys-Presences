<?php

namespace DI\helpers;

class JsonPages {
	private static array $pages = [];

	public static function addJsonPage(string $target, string $method, array $httpMethods = []) {
		if (!isset(static::$pages[$target])) {
			static::$pages[$target] = [];
		}
		if (!isset(static::$pages[$target][$method])) {
			static::$pages[$target][$method] = $httpMethods;
		} else {
			static::$pages[$target][$method] = array_merge(static::$pages[$target][$method], $httpMethods);
		}
	}

	public static function jsonPages() {
		return static::$pages;
	}
}