<?php

namespace DI\decorators;

use Attribute;
use DI\bases\AttributeBase;
use DI\helpers\HeaderBuilder;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Title extends AttributeBase {
    public function __construct(
        private string $title
    ) {}

    public function manage(): void {
        HeaderBuilder::getBuilder($this->target, $this->isMethod() ? $this->methodName : 'global')
            ->setTarget($this->target)
            ->setMethod($this->isMethod() ? $this->methodName : 'global')
            ->setTitle($this->title);
    }
}