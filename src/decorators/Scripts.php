<?php

namespace DI\decorators;

use Attribute;
use DI\bases\AttributeBase;
use DI\helpers\HeaderBuilder;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Scripts extends AttributeBase {
    public function __construct(
        private array $scripts = []
    ) {}

    public function manage(): void {
        if ($this->isMethod()) {
            HeaderBuilder::initScriptsWithGlobal($this->target, $this->methodName);
        }
        
        $methodName = $this->isMethod() ? $this->methodName : 'global';
        array_map(fn($c) => HeaderBuilder::getBuilder($this->target, $methodName)
            ->setTarget($this->target)
            ->setMethod($methodName)
            ->addScript($c), $this->scripts);
    }
}