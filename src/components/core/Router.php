<?php

namespace components\core;

use controllers\RegisterController;

class Router
{
    private function transformPathToController($path) {
        $path = ltrim($path, "/");
        $path = trim($path);
        $path = explode("?", $path)[0];
        $path = explode("/", $path);

        return $path[0];
    }

    public function route($params) {
        $path = $params[0];

        $controllerName = $this->transformPathToController($path);
        if (empty($controllerName))
            $controllerName = "Register";

        $controllerClassName = $controllerName . "Controller";

        if (file_exists("controllers/{$controllerClassName}.php")) {
            $className = "\\controllers\\"."$controllerClassName";
            $controller = new $className;
        }
        else {
            $controller = new RegisterController();
            // TODO: Handle this
        }

        $controller->viewName = strtolower($controllerName);
        $controller->render($params);

        $controller->showView();
    }
}