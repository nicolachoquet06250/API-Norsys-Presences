<?php

namespace DI\adapters\views;

use DI\decorators\Timer;
use DI\interfaces\ViewAdapter;

class Smarty implements ViewAdapter {
    private \Smarty $engine;
    private string $tpl = '';

    public function __construct(
        private string $viewsDir,
        private string $cacheDir
    ) {
        $this->engine = new \Smarty();
        $this->engine->setTemplateDir(constant('VIEW_DIR'));
        $this->engine->setCompileDir(constant('VIEW_CACHE_DIR'));
        $this->engine->setCacheDir(constant('VIEW_CACHE_DIR'));
    }

    public function setViewsDir(string $viewsDir): self {
        $this->viewsDir = $viewsDir;
        return $this;
    }
    
    public function setCacheDir(string $cacheDir): self {
        $this->cacheDir = $cacheDir;
        return $this;
    }

    public function setTpl(string $tpl): self {
        $this->tpl = $tpl;
        return $this;
    }

    public function assign(string $var, mixed $value): self {
        $this->engine->assign($var, $value);
        return $this;
    }

    #[Timer]
    public function make(array $vars = []): string {
        foreach ($vars as $key => $value) {
            $this->assign($key, $value);
        }
        return $this->engine->fetch("{$this->tpl}.tpl");
    }

    public function __call($name, $arguments) {
        return $this->engine->{$name}(...$arguments);
    }
}