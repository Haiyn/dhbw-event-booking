<?php

namespace controllers;

use components\core\Utility;
use models\User;

/**
 * Class ChatController
 * Handles initializing of the chat window
 * @package controllers
 */
class ChatController extends Controller
{
    public function render($parameters)
    {
        if (isset($_GET['user_id']) && Utility::isValidUUIDv4($_GET['user_id']) && $_GET['user_id'] != $_SESSION['user_id']) {

            $user = User::getInstance();
            $username = $user->getUserById(htmlspecialchars($_GET['user_id']))->username;
            if(empty($username)) {
                $this->redirect("/not-found");
            } else {
                $this->view->username = $username;
            }


        } else {
            $this->redirect("/not-found");
        }

        $this->view->pageTitle = "Chat with " . $username;
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }
}
