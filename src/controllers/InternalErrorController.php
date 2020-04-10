<?php

namespace controllers;

class InternalErrorController extends Controller
{
    public function render($parameters)
    {
        session_start();
        if (!empty($_SESSION['INTERNAL_ERROR'])) {
            $this->view->isMessage = true;
        }
        $this->view->pageTitle = "500 Internal Server Error";
    }
}
