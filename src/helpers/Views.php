<?php

namespace DI\helpers;

use DI\interfaces\ViewAdapter;
use DI\enums\ViewEngines;

class Views implements ViewAdapter {
    private ViewAdapter $viewEngine;
    private string $tpl = '';

    /** @var ViewAdapter[] $instances */
    private static array $instances = [];
    private static array $customizedElements = [];

    public function __construct(
        private string $viewsDir, 
        private string $cacheDir,
        private string $type
    ) {
        if (!ClassAnalyser::inEnum(ViewEngines::class, $type)) {
            throw new \TypeError("`type` parameter is not in ViewEngines enum");
        }

        $class = "\DI\adapters\\views\\$type";
        $this->viewEngine = new $class($this->viewsDir, $this->cacheDir);
    }

    public function setEngine(string $engine) {
        if (!ClassAnalyser::inEnum(ViewEngines::class, $engine)) {
            throw new \TypeError("`type` parameter is not in ViewEngines enum");
        }

        $class = "\DI\adapters\\views\\$engine";
        $this->viewEngine = new $class($this->viewsDir, $this->cacheDir);
        if (!empty($this->tpl)) {
            $this->setTpl($this->tpl);
        }
    }

    public function setViewsDir(string $viewsDir): self {
        $this->viewEngine->setViewsDir($viewsDir);
        $this->viewsDir = $viewsDir;
        return $this;
    }
    
    public function setCacheDir(string $cacheDir): self {
        $this->viewEngine->setCacheDir($cacheDir);
        $this->cacheDir = $cacheDir;
        return $this;
    }

    public function setTpl(string $tpl): self {
        $this->tpl = $tpl;
        $this->viewEngine->setTpl($tpl);
        return $this;
    }

    public function assign(string $var, mixed $value): self {
        $this->viewEngine->assign($var, $value);
        return $this;
    }

    public function make(array $vars = []): string {
        return $this->viewEngine->make($vars);
    }

    public static function addInstance(string $target, string $method, string $tpl): self {
        $method = $method === '' ? 'global' : $method;

        static::$instances[$target][$method] = (new Views(constant('VIEW_DIR'), constant('VIEW_CACHE_DIR'), constant('VIEW_ENGINE')))->setTpl($tpl);
        return static::$instances[$target][$method];
    }

    public static function addCustomizedElement(string $elementType, string $target, string $method, string $name, mixed $callback, ?string $engine) {
        if (!isset(static::$customizedElements[$engine][$elementType])) {
            static::$customizedElements[$engine][$elementType] = [];
        }
        if (!isset(static::$customizedElements[$engine][$elementType][$target])) {
            static::$customizedElements[$engine][$elementType][$target] = [];
        }
        if (!isset(static::$customizedElements[$engine][$elementType][$target][$method])) {
            static::$customizedElements[$engine][$elementType][$target][$method] = [];
        }
        static::$customizedElements[$engine][$elementType][$target][$method][$name] = $callback;
    }

    public static function customizedElement() {
        return static::$customizedElements;
    }

    public static function instances() {
        return static::$instances;
    }

    public function __call($name, $arguments) {
        return $this->viewEngine->{$name}(...$arguments);
    }
}