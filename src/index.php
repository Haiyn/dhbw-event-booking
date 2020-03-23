<?php

spl_autoload_register("autoLoader");

function autoLoader($className) {
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $file = __DIR__.DIRECTORY_SEPARATOR.$className.".php";
    if (is_readable($file)) {
        require_once $file;
    }
}

use components\core\Router;

$router = new Router();

$router->route([$_SERVER["REQUEST_URI"]]);