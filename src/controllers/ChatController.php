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
    public function render()
    {
        $this->session->checkSession();

        // Search button pressed
        if (isset($_POST['search_username'])) {
            $this->redirect("/chat?username={$_POST['search_username']}");
        }

        if (isset($_GET['username'])) {
            // Get the initiating user and the chat partner user
            $user = User::getInstance();
            $self = $user->getUserById($_SESSION['USER_ID']);
            $partner = $user->getUserByUsername(htmlspecialchars($_GET['username']));

            if(empty($partner)) {
                $this->setError("This user does not exist! Try searching for one that exists.
                You can also message people via the attendees list in an event!");
            }
            if($_GET['username'] == $self->username) {
                // User is trying to chat with themselves
                $this->redirect("/event-overview");
            }

            $this->view->partnerUserId = $partner->user_id;
            $this->view->partnerUsername = $partner->username;
            $this->view->partnerEmail = $partner->email;
            $this->view->pageTitle = "Chat with " . $partner->username;
        }

        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }
}
