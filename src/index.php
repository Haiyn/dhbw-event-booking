<?php

// Initialize autoloader
use components\core;
require_once 'components/core/Autoloader.php';

// Initialize Router
use components\core\Router;

$router = new Router();
$router->route([$_SERVER["REQUEST_URI"]]);
