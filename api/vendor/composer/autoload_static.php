<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit26c926ff7bca530ac5ce7a90db85d72f
{
    public static $prefixesPsr0 = array (
        'O' => 
        array (
            'OAuth2' => 
            array (
                0 => __DIR__ . '/..' . '/bshaffer/oauth2-server-php/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit26c926ff7bca530ac5ce7a90db85d72f::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
