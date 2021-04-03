<?php

namespace DI\helpers;

use Exception;

class ConstCreator {
	public static function create(string $jsonFile, array $required = []) {
		if (is_file($jsonFile) && strstr($jsonFile, '.json')) {
			$json = json_decode(file_get_contents($jsonFile), true);
			
			foreach ($json as $const => $value) {
				if (substr($const, 0, 1) !== '#' && !defined($const)) {
					foreach (get_defined_constants() as $_const => $_value) {
						$value = str_replace([' . ' . $_const, $_const . ' . ', ' . ' . $_const . ' . '], $_value, $value);
					}

					preg_match_all('|([\\\\A-Za-z]+\:\:[A-Za-z]+)$|D', $value, $matches);
					$value = str_replace($matches[1], array_map(fn($c) => eval('return ' . $c . ';'), $matches[1]), $value);
					//dump($const, $value);
					define($const, $value);
				}
			}

			$all_constant_defined = true;
			$missing_constants = [];
			foreach ($required as $require) {
				if (!defined($require)) {
					$all_constant_defined = false;
					$missing_constants[] = $require;
				}
			}

			if (!$all_constant_defined) {
				throw new Exception("constants ". implode(', ', $missing_constants) . " missing !!");
			}
		}
	}
}