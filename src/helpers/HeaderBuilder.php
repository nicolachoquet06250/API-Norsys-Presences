<?php

namespace DI\helpers;

class HeaderBuilder {
    /** @var HeaderBuilder[] $headerBuilders */
    private static array $headerBuilders = [];

    private string $target;
    private string $method = 'global';

    private string $title = '';
    /** @var string[] $scripts */
    private array $scripts = [];
    /** @var string[] $styles */
    private array $styles = [];

    public function setTarget(string $target): self {
        $this->target = $target;
        return $this;
    }

    public function setMethod(string $method): self {
        $this->method = $method;
        return $this;
    }

    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string[] $scripts
     * @return self
     */
    public function setScripts(array $scripts): self {
        $this->scripts = $scripts;
        return $this;
    }

    public function addScript(string $script): self {
        $this->scripts[] = $script;
        return $this;
    }

    public static function initScriptsWithGlobal(string $target, string $method): void {
        if (!empty(static::getBuilder($target, 'global')) && empty(static::getBuilder($target, $method)->scripts)) {
            static::getBuilder($target, $method)
                ->setScripts(
                    static::getBuilder($target, 'global')->scripts
                );
        }
    }

    /**
     * @param string[] $styles
     * @return self
     */
    public function setStyles(array $styles): self {
        $this->styles = $styles;
        return $this;
    }

    public function addStyle(string $style): self {
        $this->styles[] = $style;
        return $this;
    }

    public static function initStylesWithGlobal(string $target, string $method): void {
        if (!empty(static::getBuilder($target, 'global')) && empty(static::getBuilder($target, $method)->styles)) {
            static::getBuilder($target, $method)
                ->setStyles(
                    static::getBuilder($target, 'global')->styles
                );
        }
    }

    public function build(bool $forViewEngine = false): string|array {
        if ($this->method !== 'global') {
            static::getBuilder($this->target, $this->method);
            static::initScriptsWithGlobal($this->target, $this->method);
            static::initStylesWithGlobal($this->target, $this->method);
        }

        $title = !empty($this->title) ? "\n<title>" . $this->title . "</title>" : '';
        $scripts = implode("\n", array_map(fn($s) => "<script src='$s'></script>", $this->scripts));
        $styles = implode("\n", array_map(fn($s) => "<link rel='stylesheet' href='$s' />", $this->styles));

        if ($forViewEngine) {
            $array = [];
            if (!empty($this->title)) {
                $array['title'] = $this->title;
            }
            $array['scripts'] = $this->scripts;
            $array['styles'] = $this->styles;
            return $array;
        }

        return <<<HTML
            <head>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta charset="utf-8" />{$title}
                {$scripts}
                {$styles}
            </head>
        HTML;
    }

    public static function addBuilder(string $target, HeaderBuilder $builder, string $method = 'global') {
        static::$headerBuilders[$target][$method] = $builder;
    }

    public static function builders(): array {
        return static::$headerBuilders;
    }

    /**
     * @param string $target
     * @param string $method
     * @return self
     */
    public static function getBuilder(string $target, string $method = 'global'): self {
        if ($method === '') $method = 'global';
        if (isset(static::$headerBuilders[$target][$method])) {
            return static::$headerBuilders[$target][$method];
        } else {
            $builder = new HeaderBuilder();
            static::$headerBuilders[$target][$method] = $builder;
            return static::$headerBuilders[$target][$method];
        }
    }
}