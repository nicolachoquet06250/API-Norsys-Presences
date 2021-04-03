<?php


namespace DI\injection;

use ReflectionClass;
use ReflectionObject;
use ReflectionFunction;

class InjectionContainer {
    private static $correspondences = [];

    public static function addDependencyWithInterface(string $interface, string $class): void {
        static::$correspondences[$interface] = $class;
    }

    public static function addDependency(string $class): void {
        static::$correspondences[$class] = $class;
    }

    public static function correspondences(): array {
        return static::$correspondences;
    }

    public function injectInConstruct(string $class, ...$additional_parameters) {
        $rc = new ReflectionClass($class);
        $param_types = [];
        if (!is_null($rc->getConstructor())) {
            $param_types = array_reduce(
                $rc->getConstructor()->getParameters(),
                function ($r, $c) {
                    if (!in_array($c->getType()->getName(), ['string', 'int', 'float', 'bool', 'array', 'object', 'callable'])) {
                        if (isset(InjectionContainer::correspondences()[$c->getType()->getName()])) {
                            $r[] = $this->injectInConstruct(InjectionContainer::correspondences()[$c->getType()->getName()]);
                        } else if (isset(InjectionContainer::correspondences()['\\' . $c->getType()->getName()])) {
                            $r[] = $this->injectInConstruct(InjectionContainer::correspondences()['\\' . $c->getType()->getName()]);
                        }
                    }
                    return $r;
                }, []
            );
        }

        return new $class(...$param_types, ...$additional_parameters);
    }

    public function injectIntoMethod($object, string $method) {
        $ro = new ReflectionObject($object);
        if ($ro->hasMethod($method)) {
            $param_types = array_reduce(
                $ro->getMethod($method)->getParameters(),
                function ($r, $c) {
                    if (!in_array($c->getType()->getName(), ['string', 'int', 'float', 'bool', 'array', 'object', 'callable'])) {
                        if (isset(InjectionContainer::correspondences()[$c->getType()->getName()])) {
                            $r[] = $this->injectInConstruct(InjectionContainer::correspondences()[$c->getType()->getName()]);
                        } else if (isset(InjectionContainer::correspondences()['\\' . $c->getType()->getName()])) {
                            $r[] = $this->injectInConstruct(InjectionContainer::correspondences()['\\' . $c->getType()->getName()]);
                        }
                    }
                    return $r;
                }, []
            );

            return $object->$method(...$param_types);
        }
        $class = $object::class;
        throw new \Exception("Method $class::$method() not found");
    }

    public function injectIntoFunction(callable $function) {
        return function() use ($function) {
            $rf = new ReflectionFunction($function);
            $param_types = array_reduce(
                $rf->getParameters(),
                function ($r, $c) {
                    if (!in_array($c->getType()->getName(), ['string', 'int', 'float', 'bool', 'array', 'object', 'callable'])) {
                        if (isset(InjectionContainer::correspondences()[$c->getType()->getName()])) {
                            $r[] = $this->injectInConstruct(InjectionContainer::correspondences()[$c->getType()->getName()]);
                        } else if (isset(InjectionContainer::correspondences()['\\' . $c->getType()->getName()])) {
                            $r[] = $this->injectInConstruct(InjectionContainer::correspondences()['\\' . $c->getType()->getName()]);
                        }
                    }
                    return $r;
                }, []
            );

            return $function(...$param_types);
        };
    }
}
