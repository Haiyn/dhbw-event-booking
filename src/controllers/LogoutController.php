<?php

namespace controllers;

class LogoutController extends Controller
{
    public function render($params)
    {
        $this->session->unsetSession();
    }
}

