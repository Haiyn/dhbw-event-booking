<?php


namespace controllers;

use models\User;
use models\Event;

class ProfileController extends Controller {

    public function render($params)
    {
//        $this->session->checkSession();

        /*$this->displayUserInfo();
        $this->displayOwnerEvents();
        $this->displayBookedEvents();*/

        $this->view->pageTitle = "Profile";
    }


    private function displayUserInfo($data){

        if (isset($_SESSION["USER_ID"])){
            $user = User::getInstance();

            $thisUserId = $_SESSION["USER_ID"];
            $thisUser = $user->getUserById($thisUserId);
            $username = $thisUser->username;
            $firstName = $thisUser->first_name;
            $lastName = $thisUser->last_name;
            $email = $thisUser->email;
        }
//        return
    }


    /**
     * Checks if user id from session is equal to the id of the creator of an event
     * While they are equal, return all events created by current user
     * */
    private function displayOwnerEvents($event){
        if (isset($_SESSION["USER_ID"])){

            $user = User::getInstance();
            $thisUserId = $_SESSION["USER_ID"];
            $thisUser = $user->getUserById($thisUserId);

            $creator = $user->getUserById($event->creator_id);

            while ($thisUser === $creator){
                $event = Event::getInstance();
                $events = $event->getEvents();
            }
            return $events;
        }
    }

    /**
     * Displays all events that have been booked by current user
     * */
    private function displayBookedEvents(){

    }




}