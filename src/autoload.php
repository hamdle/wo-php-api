<?php

/*
 * autoload.php: custom autoload files
 *
 * Copyright (C) 2021 Eric Marty
 */

require_once __DIR__.'/Utils/Env.php';

use Utils\Env;

class Autoload {
    /*
     * Load .env and register custom autoloader.
     * @return void
     */
    public static function register()
    {
        Env::load();
        spl_autoload_register('Autoload::loadFile');
    }

    /*
     * Custom autoload algorithm.
     * @param $class - string
     * @return bool
     */
    public static function loadFile($class)
    {
        $file = __DIR__.DIRECTORY_SEPARATOR.
            str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';

        if (file_exists($file))
        {
            require_once $file;
            return true;
        }

        return false;
    }
}

Autoload::register();
