<?php

namespace DI\helpers;

use ReflectionClass;

abstract class ClassAnalyser {
    public static function inEnum(string $type, mixed $value): bool {
        $rc = new ReflectionClass($type);
        return array_reduce($rc->getConstants(), fn(bool $r, string $c) => ($c === $value) ? true : $r, false);
    }
}