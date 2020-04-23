<?php

namespace controllers;

use models\User;

class LogoutController extends Controller
{

    public function render($params)
    {
        $this->session->checkSession();

        if (isset($_POST['logout'])) {
            $this->session->unsetSession();
            $this->redirect("login");
        }

    }

}
