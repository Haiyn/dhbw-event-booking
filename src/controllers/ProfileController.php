<?php


namespace controllers;

use models\Booking;
use models\User;
use models\Event;

class ProfileController extends Controller
{

    public function render($params)
    {
        $this->session->checkSession();

        if (isset($_POST["user_id"])) {
            $user = User::getInstance();
            $userId = $user->getUserById(htmlspecialchars($_POST["user_id"]));

            $this->displayUserInfo($userId);
            $this->displayCreatorEvents($userId);
            $this->displayBookedEvents($userId);
        }

        $this->view->pageTitle = "Profile";
    }


    private function displayUserInfo($userId)
    {

        if (isset($userId)) {
            $user = User::getInstance();

            $thisUser = $user->getUserById($userId);
            $username = $thisUser->username;
            $firstName = $thisUser->first_name;
            $lastName = $thisUser->last_name;
            $email = $thisUser->email;

            $this->view->username = $username;
            $this->view->firstName = $firstName;
            $this->view->lastName = $lastName;
            $this->view->email = $email;
        }
    }


    /**
     * Checks if user id from session is equal to the id of the creator of an event
     * While they are equal, return all events created by current user
     * @param $userId
     */
    private function displayCreatorEvents($userId)
    {
        if (isset($userId)) {
            $user = User::getInstance();
            $thisUser = $user->getUserById($userId);

            if (isset($_GET["event_id"])) {
                $event = Event::getInstance();
                $eventId = $event->getEventById(htmlspecialchars($_GET['event_id']));

                if (isset($eventId->creator_id)) {
                    $creator = $user->getUserById($eventId->creator_id);

                    while ($thisUser === $creator) {
                        $events = $event->getEvents();
                        $this->view->events = $events;
                    }
                }
            }
        }
    }


    /**
     * Displays all events that have been booked by current user
     * @param $userId
     */
    private function displayBookedEvents($userId)
    {
        if (isset($userId)) {
            $user = User::getInstance();
            $thisUser = $user->getUserById($userId);


            $booking = Booking::getInstance();
            $attendee = $booking->getBookingsByUserId($userId->user_id);


            while ($thisUser === $attendee) {
                $events = $booking->getEvents();
                $this->view->events = $events;
            }
        }

    }
//NEUE DATENBANK ABRFRAGE
//        povezat sa userom na booking.php


}