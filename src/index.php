<?php

// Initialize autoloader
use components\core\Autoloader;
require_once 'components/core/Autoloader.php';

$autoloader = new Autoloader();
$autoloader->register();

// Initialize Router
use components\core\Router;

$router = new Router();
$router->route([$_SERVER["REQUEST_URI"]]);
