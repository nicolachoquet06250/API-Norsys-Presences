<?php

namespace DI\bases;

abstract class AttributeBase {
    protected string $target;
    protected string $methodName = '';

    public final function setTarget(string $target): void {
        $this->target = $target;
    }

    public final function setMethod(string $methodName): void {
        $this->methodName = $methodName;
    }

    protected final function isMethod(): bool {
        return empty($this->methodName) === false;
    }

    public abstract function manage(): void;
}