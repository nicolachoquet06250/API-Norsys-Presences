<?php

namespace DI\router;

use ReflectionClass;
use ReflectionMethod;
use DI\decorators\{
    CustomIf,
    Directive,
    Json,
    MethodNotAllowed,
    PageNotFound,
    Route, Title,
    Scripts, Stylesheets,
    View
};
use DI\helpers\{
    Attribute, DirAnalyser
};

class Router {
    private static array $enabledAttributes = [
        Json::class,
        Route::class,
        PageNotFound::class,
        MethodNotAllowed::class,
        View::class,
        CustomIf::class,
        Directive::class,
        Title::class,
        Scripts::class,
        Stylesheets::class,
    ];

    public static function analyse() {
        DirAnalyser::analyse(realpath(__ROOT__.'/src'), function($root, $elem) {
            $class = str_replace(
                [ realpath(__ROOT__), 'src', '/', '.php' ], 
                [ '', 'DI', '\\', '' ], 
                $root.'/'.$elem
            );

            $rc = new ReflectionClass($class);
            
            foreach (static::$enabledAttributes as $enabledAttribute) {
                if (!empty($rc->getAttributes($enabledAttribute))) {
                    foreach ($rc->getAttributes($enabledAttribute) as $attribute) 
                        Attribute::manage($attribute, $class);
                }
            }

            foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $rm) {
                foreach (static::$enabledAttributes as $enabledAttribute) {
                    if (!empty($rm->getAttributes($enabledAttribute))) {
                        foreach ($rm->getAttributes($enabledAttribute) as $attribute) 
                            Attribute::manage($attribute, $class, methodName: $rm->getName());
                    }
                }
            }
        });
    }
}