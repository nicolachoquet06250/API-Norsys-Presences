<?php

namespace DI\decorators;

use Attribute;
use DI\helpers\Views;
use DI\bases\AttributeBase;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class View extends AttributeBase {
    public function __construct(
        private string $tpl,
        private ?string $engine = null
    ) {}

    public function manage(): void {
        $method = $this->isMethod() ? $this->methodName : 'global';
        $view = Views::addInstance($this->target, $method, $this->tpl);
        if (!is_null($this->engine)) {
            $view->setEngine($this->engine);
        }
    }
}