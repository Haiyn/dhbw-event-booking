<?php

namespace controllers;

class NotFoundController extends Controller
{
    public function render($parameters)
    {
        $this->_view->pageTitle = "404 Not Found";
    }
}