<?php

namespace controllers;

use models\User;

class ConfirmController extends Controller
{
    public function render($parameters)
    {
        session_start();
        if(isset($_GET['hash']))
        {
            // Update the verified field in the database
            $user = User::newInstance();
            $user->confirmUser(htmlspecialchars($_GET['hash']));

            $this->setSuccess("Your email was successfully confirmed!");
        }
        else
        {
            if(!isset($_GET['success']) && !isset($_GET['error'])) {
                $this->setError("Sorry, something went wrong!");
            }
        }

        $this->view->pageTitle = "Email Confirmation";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }
}