<?php

namespace DI\adapters\views;

use DI\interfaces\ViewAdapter;

class Blade implements ViewAdapter {
    private \Jenssegers\Blade\Blade $engine;
    private array $vars = [];
    private string $tpl = '';

    public function __construct(
        private string $viewsDir,
        private string $cacheDir
    ) {
        $this->engine = new \Jenssegers\Blade\Blade($this->viewsDir, $this->cacheDir);
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
        $this->vars[$var] = $value;
        return $this;
    }

    public function make(array $vars = []): string {
        $view = $this->engine->make($this->tpl, $vars);
        foreach ($this->vars as $var => $value) $view->with($var, $value);
        return (string)$view;
    }

    public function __call($name, $arguments) {
        return $this->engine->{$name}(...$arguments);
    }
}