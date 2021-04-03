<?php

namespace DI\interfaces;

interface ViewAdapter {
	public function setViewsDir(string $viewsDir): self;
    
    public function setCacheDir(string $cacheDir): self;

    public function setTpl(string $tpl): self;

    public function assign(string $var, mixed $value): self;

    public function make(array $vars = []): string;

    public function __call($name, $arguments);
}