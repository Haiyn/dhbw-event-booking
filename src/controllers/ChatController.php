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
        $this->session->checkSession();
        // user_id needs to be set, a valid UUIDv4 and not the logged in user
        if (isset($_GET['user_id']) && Utility::isValidUUIDv4($_GET['user_id']) && $_GET['user_id'] != $_SESSION['user_id']) {

            $chatPartnerID = htmlspecialchars($_GET['user_id']);

            $partner = $this->getChatPartnerInformation($chatPartnerID);

            // If the partner information is empty, the user id does not exist in the database
            if(empty($partner['username'])) {
                $this->redirect("/not-found");
            }

            $this->view->username = $partner['username'];
            $this->view->pageTitle = "Chat with " . $partner['username'];

        } else {
            $this->redirect("/not-found");
        }


        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }

    private function getChatPartnerInformation($userID) {
        // Get the username of the chat partner
        $partnerInformation = [];
        $user = User::getInstance();

        $partnerInformation['username'] = $user->getUserById($userID)->username;

        return $partnerInformation;
    }
}
