<?php

namespace DI\decorators;

use Attribute;
use DI\helpers\Views;
use DI\bases\AttributeBase;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Directive extends AttributeBase {
    public function __construct(
        private string $name, 
        private mixed $callback,
        private ?string $engine = null
    ) {}

    public function manage(): void {
        if (file_exists(__ROOT__ . '/' . explode('::', str_replace(['DI', '\\'], ['src', '/'], 'DI\views\\' . strtolower(empty($this->engine) ? VIEW_ENGINE : $this->engine) . '\custom\\' . $this->callback))[0] . '.php')) {
            $callback = 'DI\views\\' . strtolower(empty($this->engine) ? VIEW_ENGINE : $this->engine) . '\custom\\' . $this->callback;

            Views::addCustomizedElement('directive', $this->target, $this->isMethod() ? $this->methodName : 'global', $this->name, $callback, empty($this->engine) ? VIEW_ENGINE : $this->engine);
        }
    }
}