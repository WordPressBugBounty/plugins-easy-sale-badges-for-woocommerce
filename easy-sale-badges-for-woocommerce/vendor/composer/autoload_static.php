<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit853a2847bad9f52783cabb6a081e1105
{
    public static $files = array (
        'f01f3124ff17b61f705eb8e9e7f7bd36' => __DIR__ . '/../..' . '/src/Helpers.php',
        'e3efe5bd37f066622f2ad3e67215feff' => __DIR__ . '/../..' . '/src/Helpers/Badges.php',
    );

    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'AsanaPlugins\\WooCommerce\\SaleBadges\\' => 36,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'AsanaPlugins\\WooCommerce\\SaleBadges\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit853a2847bad9f52783cabb6a081e1105::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit853a2847bad9f52783cabb6a081e1105::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit853a2847bad9f52783cabb6a081e1105::$classMap;

        }, null, ClassLoader::class);
    }
}