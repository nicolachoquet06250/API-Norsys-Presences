<?php

namespace DI\decorators;

use Attribute;
use DI\bases\AttributeBase;
use DI\helpers\JsonPages;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Json extends AttributeBase {
	public function __construct(
		private array $forHttpMethods = ['get']
	) {}

	public function manage(): void {
		JsonPages::addJsonPage($this->target, $this->isMethod() ? $this->methodName: 'global', $this->forHttpMethods);
	}
}