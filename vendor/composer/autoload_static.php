<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2871242cb392c30e36809da7fdc5e407
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'src\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'src\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'src\\Controller\\GameController' => __DIR__ . '/../..' . '/src/Controller/GameController.php',
        'src\\Model\\GameModel' => __DIR__ . '/../..' . '/src/Model/GameModel.php',
        'src\\View\\GameView' => __DIR__ . '/../..' . '/src/View/GameView.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2871242cb392c30e36809da7fdc5e407::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2871242cb392c30e36809da7fdc5e407::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2871242cb392c30e36809da7fdc5e407::$classMap;

        }, null, ClassLoader::class);
    }
}
