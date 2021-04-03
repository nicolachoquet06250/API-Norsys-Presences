<?php

namespace DI\helpers;

use ReflectionAttribute;
use DI\bases\AttributeBase;

abstract class Attribute {
    public static function manage(ReflectionAttribute $attribute, string $target, string $methodName = ''): void {
        /** @var AttributeBase $attr */
        $attr = $attribute->newInstance();
        $attr->setTarget($target);
        $attr->setMethod($methodName);
        $attr->manage();
    }
}