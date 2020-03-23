<?php

namespace controllers;

use stdClass;

abstract class Controller
{
    public $viewName;
    protected $view;

    function __construct() {
        $this->view = new stdClass();
    }

    abstract public function render($params);

    protected final function redirect($url) {
        header("Location: $url");
        header("Connection: close");
        exit;
    }

    public final function showView() {
        extract((array)$this->view);
        require dirname(__DIR__)."/views/{$this->viewName}/{$this->viewName}.phtml";
    }
}