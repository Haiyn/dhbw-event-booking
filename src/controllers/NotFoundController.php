<?php

namespace controllers;

/**
 * Class NotFoundController
 * Description not found
 * @package controllers
 */
class NotFoundController extends Controller
{
    public function render($parameters)
    {
        $this->view->pageTitle = "404 Not Found";
    }
}
