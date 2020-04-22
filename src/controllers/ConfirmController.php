<?php

namespace controllers;

use models\User;

/**
 * Class ConfirmController
 * Handles confirming of email addresses via the verifaction hash of a user.
 * @package controllers
 */
class ConfirmController extends Controller
{
    public function render($parameters)
    {
        if (isset($_GET['hash'])) {
            // Update the verified field in the database
            $user = User::getInstance();
            $user->confirmUser(htmlspecialchars($_GET['hash']));

            $this->setSuccess("Your email was successfully confirmed!");
        } else {
            if (!isset($_GET['success']) && !isset($_GET['error'])) {
                $this->setError("Sorry, something went wrong!");
            }
        }

        $this->view->pageTitle = "Email Confirmation";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }
}
