<?php

namespace controllers;

class LogoutController extends Controller
{
    public function render()
    {
        $this->session->unsetSession();
    }
}
