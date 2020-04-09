<?php

namespace controllers;

class LogoutController extends Controller
{

    public function render($params)
    {
        if (isset($_POST['logout'])) {
            $this->session->unsetSession();
        }

    }
}

