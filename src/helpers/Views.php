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
        $this->viewEngine = Timer::create($class, '__construct', $this->viewsDir, $this->cacheDir);
    }

    public function setEngine(string $engine) {
        if (!ClassAnalyser::inEnum(ViewEngines::class, $engine)) {
            throw new \TypeError("`type` parameter is not in ViewEngines enum");
        }

        $class = "\DI\adapters\\views\\$engine";
        $this->viewEngine = Timer::create($class, '__construct', $this->viewsDir, $this->cacheDir);
        if (!empty($this->tpl)) {
            Timer::create($this, 'setTpl', $this->tpl);
        }
    }

    public function setViewsDir(string $viewsDir): self {
        $this->viewsDir = $viewsDir;
        Timer::create($this->viewEngine, 'setViewsDir', $viewsDir);
        return $this;
    }
    
    public function setCacheDir(string $cacheDir): self {
        $this->cacheDir = $cacheDir;
        Timer::create($this->viewEngine, 'setCacheDir', $cacheDir);
        return $this;
    }

    public function setTpl(string $tpl): self {
        $this->tpl = $tpl;
        Timer::create($this->viewEngine, 'setTpl', $tpl);
        return $this;
    }

    public function assign(string $var, mixed $value): self {
        Timer::create($this->viewEngine, 'assign', $var, $value);
        return $this;
    }

    public function make(array $vars = []): string {
        return Timer::create($this->viewEngine, 'make', $vars);
    }

    public static function addInstance(string $target, string $method, string $tpl, ?string $engine = null): array {
        $method = $method === '' ? 'global' : $method;

        if (!is_null($engine) && !ClassAnalyser::inEnum(ViewEngines::class, $engine)) {
            throw new \TypeError("`type` parameter is not in ViewEngines enum");
        }

        if (is_null($engine)) {
            $engine = constant('VIEW_ENGINE');
        }

        static::$instances[$target][$method] = [
            'view_engine' => $engine,
            'view_dir' => constant('VIEW_DIR'),
            'view_cache_dir' => constant('VIEW_CACHE_DIR'),
            'tpl' => $tpl,
            'instance' => null
        ];
        /*Timer::create(
            Timer::create(
                Views::class, 
                '__construct', 
                constant('VIEW_DIR'), constant('VIEW_CACHE_DIR'), constant('VIEW_ENGINE')
            ), 
            'setTpl', 
            $tpl
        );*/
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

    public static function setInstance(string $target, string $method): self {
        static::$instances[$target][$method]['instance'] = Timer::create(
            Timer::create(
                Views::class, 
                '__construct', 
                static::$instances[$target][$method]['view_dir'], static::$instances[$target][$method]['view_cache_dir'], static::$instances[$target][$method]['view_engine']
            ), 
            'setTpl', 
            static::$instances[$target][$method]['tpl']
        );
        return static::$instances[$target][$method]['instance'];
    }

    public function __call($name, $arguments) {
        return Timer::create($this->viewEngine, $name, ...$arguments);
    }
}