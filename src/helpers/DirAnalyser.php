<?php

namespace DI\helpers;

class DirAnalyser {
    public static function analyse(string $root, callable $callback) {
        $dir = opendir($root);

        while (($elem = readdir($dir)) !== false) {
            if ($elem !== '.' && $elem !== '..' && $elem !== 'vendor' && $elem !== 'index.php' && $elem !== 'composer.json') {
               if (is_dir($root . '/' . $elem)) {
                    static::analyse($root . '/' . $elem, $callback);
                } else {
                    $callback($root, $elem);
                }
            }
        }
    }
}