<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit25aaf48d3ffa49f9f8080ae7f12e31d9
{
    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Phpml' => 
            array (
                0 => __DIR__ . '/..' . '/php-ai/php-ml/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit25aaf48d3ffa49f9f8080ae7f12e31d9::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}