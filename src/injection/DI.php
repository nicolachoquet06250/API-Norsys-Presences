<?php

namespace DI\injection;

use ReflectionClass;
use DI\helpers\{
    Attribute, DirAnalyser
};
use DI\decorators\Injectable;

class DI {
    public static function analyze($root) {
        if (is_dir($root)) {
            DirAnalyser::analyse($root, function($root, $elem) {
                $class = str_replace(
                    [ __ROOT__, 'src', '/', '.php' ], 
                    [ '', 'DI', '\\', '' ], 
                    $root.'/'.$elem
                );
                $rc = new ReflectionClass($class);
                if (!empty($rc->getAttributes(Injectable::class))) {
                    foreach ($rc->getAttributes(Injectable::class) as $attribute) {
                        Attribute::manage($attribute, $class);
                    }
                }
            });
        }
    }
}