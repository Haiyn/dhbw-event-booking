<?php

namespace controllers;

use models\Event;
use models\User;

/**
 * Class ProfileController
 * Displays the users profile
 * @package controllers
 */
class ProfileController extends Controller
{
    public function render()
    {
        $this->session->checkSession();

        $this->displayUserInfo();
        $this->displayCreatorEvents();

        $this->view->pageTitle = "Profile";
    }

    /**
     * Displays personal information of currently logged in user
     */
    private function displayUserInfo()
    {

        $user = User::getInstance();
        $userById = $user->getUserById($_SESSION['USER_ID']);

        $username = $userById->username;
        $firstName = $userById->first_name;
        $lastName = $userById->last_name;
        $email = $userById->email;

        $this->view->username = $username;
        $this->view->firstName = $firstName;
        $this->view->lastName = $lastName;
        $this->view->email = $email;
    }

    /**
     * Checks if user id from session is equal to the id of the creator of an event
     * While they are equal, return all events created by current user
     */
    private function displayCreatorEvents()
    {

        $event = Event::getInstance();
        $events = $event->getEvents();

        $creator_events = [];
        foreach ($events as $event) {
            if ($event->creator_id == $_SESSION['USER_ID']) {
                array_push($creator_events, $event);
                continue;
            }
        }

        $this->view->events = $creator_events;
    }
}
