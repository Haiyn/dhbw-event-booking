<?php

namespace components\core;

function autoLoader($className) {
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $file = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $className . ".php";
    if (is_readable($file)) {
        require_once $file;
    }
}

spl_autoload_register("components\\core\\autoLoader");
