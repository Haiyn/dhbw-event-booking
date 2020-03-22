<?php

use components\core\Router;

$router = new Router();

$router->route([$_SERVER["REQUEST_URI"]]);