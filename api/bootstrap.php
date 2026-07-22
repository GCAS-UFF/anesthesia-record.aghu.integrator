<?php

/**
 * --------------------------------------------------------------------------
 * Autoload de Classes
 * --------------------------------------------------------------------------
 * Carrega automaticamente classes da aplicação sem necessidade de Composer.
 */

spl_autoload_register(function (string $class) {

    $directories = [
        __DIR__ . '/core/',
        __DIR__ . '/repositories/',
        __DIR__ . '/controllers/',
	    __DIR__ . '/services/'
    ];

    foreach ($directories as $directory) {

        $file = $directory . $class . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});