<?php

namespace DI\decorators;

use Attribute;
use DI\helpers\{
    Views,
    HeaderBuilder,
    JsonPages
};
use DI\bases\AttributeBase;
use DI\interfaces\ViewAdapter;
use DI\injection\InjectionContainer;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Route extends AttributeBase {
    public function __construct(
        private string $route, 
        private string $method = 'get', 
        private array $methods = ['get']
    ) {}

    private function manage_directive($detail) {
        if (isset($detail[$this->target][$this->isMethod() ? $this->methodName : 'global'])) {
            foreach ($detail[$this->target][$this->isMethod() ? $this->methodName : 'global'] as $name => $callback) {
                Views::instances()[$this->target][$this->isMethod() ? $this->methodName : 'global']->directive($name, fn(...$expr) => $callback(...$expr));
            }
        }
    }

    public function manage(): void {
        if ($this->isMethod()) {
            \DI\router\Route::add($this->route, function(...$parameters) {
                $isJsonForMethods = empty(JsonPages::jsonPages()[$this->target][$this->methodName]) ? [] : JsonPages::jsonPages()[$this->target][$this->methodName];
                if (count($this->methods) > 1 || $this->methods[0] !== 'get') {
                    $isJson = array_reduce($isJsonForMethods, function($red, $cur) {
                        $tmp = false;
                        foreach ($this->methods as $m) {
                            if ($m === $cur) {
                                $tmp = true;
                                break;
                            }
                        }
                        if ($tmp === true) $red = $tmp;
                        return $red;
                    }, false);
                } else {
                    $isJson = in_array($this->method, $isJsonForMethods);
                }

                $injectionContainer = new InjectionContainer();
                $obj = $injectionContainer->injectInConstruct($this->target, ...$parameters);
                $headerBuilder = HeaderBuilder::getBuilder($this->target, $this->methodName);

                foreach (Views::customizedElement() as $engine => $detail) {
                    foreach ($detail as $type => $elems) {
                        if (method_exists($this, "manage_$type")) {
                            call_user_func([$this, "manage_$type"], $elems);
                        }
                    }
                }

                $result = $injectionContainer->injectIntoMethod($obj, $this->methodName, ...$parameters);
                if (is_array($result) && isset(Views::instances()[$this->target][$this->methodName])) {
                    /** @var ViewAdapter $view */
                    $view = Views::instances()[$this->target][$this->methodName];
                    $result = array_merge($headerBuilder->build(forViewEngine: true), $result);
                    echo $view->make($result);
                } elseif (is_array($result) && !isset(Views::instances()[$this->target][$this->methodName]) && !$isJson) {
                    dump($result);
                } elseif (is_array($result) && !isset(Views::instances()[$this->target][$this->methodName]) && $isJson) {
                    header('Content-Type: application/json');
                    echo json_encode($result);
                } elseif(is_string($result)) {
                    echo $headerBuilder->build();
                    echo $result;
                }
            }, (count($this->methods) > 1 || $this->methods[0] !== 'get' ? $this->methods : $this->method));
        } else {
            foreach ($this->methods as $method) {
                $route = $this->route;
                if (in_array($method, ['post', 'put', 'delete'])) $route .= '/([0-9]*)';

                \DI\router\Route::add($route, function(...$parameters) use($method) {$isJsonForMethods = empty(JsonPages::jsonPages()[$this->target][$this->methodName]) ? [] : JsonPages::jsonPages()[$this->target][$this->methodName];
                    $isJsonForMethods = empty(JsonPages::jsonPages()[$this->target]['global']) ? [] : JsonPages::jsonPages()[$this->target]['global'];
                    $isJson = in_array($this->method, $isJsonForMethods);

                    $injectionContainer = new InjectionContainer();
                    $obj = $injectionContainer->injectInConstruct($this->target, ...$parameters);
                    $headerBuilder = HeaderBuilder::getBuilder($this->target, 'global');

                    $result = $injectionContainer->injectIntoMethod($obj, $method, ...$parameters);
                    if (is_array($result) && isset(Views::instances()[$this->target][$method])) {
                        /** @var ViewAdapter $view */
                        $view = Views::instances()[$this->target][$method];
                        $result = array_merge($headerBuilder->build(forViewEngine: true), $result);
                        echo $view->make($result);
                    } elseif (is_array($result) && !isset(Views::instances()[$this->target]['global']) && !$isJson) {
                        dump($result);
                    } elseif (is_array($result) && !isset(Views::instances()[$this->target]['global']) && $isJson) {
                        header('Content-Type: application/json');
                        echo json_encode($result);
                    } elseif(is_string($result)) {
                        echo $headerBuilder->build();
                        echo $result;
                    }
                }, $method);
            }
        }
    }
}