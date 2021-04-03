<?php

namespace DI\decorators;

use Attribute;
use DI\bases\AttributeBase;
use DI\injection\InjectionContainer;

#[Attribute(Attribute::TARGET_CLASS)]
class Injectable extends AttributeBase {
    public function __construct(
        private string $associatedInterface = ''
    ) {}

    public function manage(): void {
        if ($this->associatedInterface === '') {
            InjectionContainer::addDependency($this->target);
        } else {
            InjectionContainer::addDependencyWithInterface($this->associatedInterface, $this->target);
        }
    }
}
