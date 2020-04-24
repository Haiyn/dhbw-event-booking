<?php

namespace controllers;

use models\User;

class LogoutController extends Controller
{

    public function render($params)
    {
        $this->session->unsetSession();

    }

}

