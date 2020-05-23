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
    public function render()
    {
        if (isset($_GET['hash'])) {

            $hash = htmlspecialchars($_GET['hash']);
            $user = User::getInstance();

            // Confirm the user in the database
            if ($user->confirmUser($hash)) {
                $this->setSuccess("Your email was successfully confirmed!");
            } else {
                $this->setError("Sorry, we couldn't confirm your email!");
            }


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
