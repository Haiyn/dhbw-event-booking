<?php

namespace controllers;

class InternalErrorController extends Controller
{
    public function render($parameters)
    {
        $this->_view->pageTitle = "500 Internal Server Error";
    }
}