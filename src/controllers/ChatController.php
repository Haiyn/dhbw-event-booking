<?php

namespace controllers;

use components\core\Utility;
use models\Message;
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
        // user_id needs to be set, a valid UUIDv4 and not the logged in user
        if (isset($_GET['user_id']) && Utility::isValidUUIDv4($_GET['user_id']) && $_GET['user_id'] != $_SESSION['user_id']) {

            $chatPartnerID = htmlspecialchars($_GET['user_id']);

            $partner = $this->getChatPartnerInformation($chatPartnerID);

            // If the partner information is empty, the user id does not exist in the database
            if(empty($partner['username'])) {
                $this->redirect("/not-found");
            }

            $messages = $this->getMessages($chatPartnerID);

            //$message = Message::getInstance();

            $this->view->username = $partner['username'];
            $this->view->pageTitle = "Chat with " . $partner['username'];
            $this->view->messages = $messages;

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

    private function getMessages($userID) {

/*        $message = Message::getInstance();

        // Get all messages (inbound and outbound)
        $inbound = $message->getMessagesByUserIdDirection($userID, $_SESSION['USER_ID']);
        $outbound = $message->getMessagesByUserIdDirection($_SESSION['USER_ID'], $userID);
        $messages = $inbound + $outbound;

        // Sort the messages by oldest to newest
        $messages = usort($messages, function($entry1, $entry2) {
            $v1 = strtotime($entry1['time_sent']);
            $v2 = strtotime($entry2['time_sent']);
            return $v1 - $v2;
        });

        return $messages;*/
return null;
    }
}
