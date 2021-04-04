<?php

namespace DI\decorators;

use Attribute;
use DI\helpers\{
	Views, HeaderBuilder
};
use DI\bases\AttributeBase;
use DI\injection\InjectionContainer;

#[Attribute(Attribute::TARGET_METHOD)]
class PageNotFound extends AttributeBase {
	private function manage_directive($detail) {
        if (isset($detail[$this->target][$this->isMethod() ? $this->methodName : 'global'])) {
            foreach ($detail[$this->target][$this->isMethod() ? $this->methodName : 'global'] as $name => $callback) {
                Views::instances()[$this->target][$this->isMethod() ? $this->methodName : 'global']->directive($name, fn(...$expr) => $callback(...$expr));
            }
        }
    }

	public function manage(): void {
		\DI\router\Route::pathNotFound(function(string $path) {
			http_response_code(404);

			$injectionContainer = new InjectionContainer();
			$obj = $injectionContainer->injectInConstruct($this->target, $path);
			$headerBuilder = HeaderBuilder::getBuilder($this->target, $this->methodName);

			foreach (Views::customizedElement() as $engine => $detail) {
				foreach ($detail as $type => $elems) {
					if (method_exists($this, "manage_$type")) {
						call_user_func([$this, "manage_$type"], $elems);
					}
				}
			}

			$result = $injectionContainer->injectIntoMethod($obj, $this->methodName, $path);
			if (is_array($result) && isset(Views::instances()[$this->target][$this->methodName])) {
				/** @var ViewAdapter $view */
				$view = Views::instances()[$this->target][$this->methodName];
				$result = array_merge($headerBuilder->build(forViewEngine: true), $result);
				echo $view->make($result);
			} elseif (is_array($result) && !isset(Views::instances()[$this->target][$this->methodName])) {
				dump($result);
			} elseif(is_string($result)) {
				echo $headerBuilder->build();
				echo $result;
			}
		});
	}
}