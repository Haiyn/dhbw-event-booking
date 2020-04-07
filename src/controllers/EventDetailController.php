<?php

namespace controllers;

use models\Booking;
use models\Event;
use models\User;

class EventDetailController extends Controller
{
    public function render($params)
    {
        $this->session->checkSession();

        if (isset($_GET['event_id'])) {
            $event = Event::getInstance();
            $eventById = $event->getEventById(htmlspecialchars($_GET['event_id']));

            $user = User::getInstance();
            if (isset($eventById->creator_id)) {
                $creator = $user->getUserById($eventById->creator_id);
            }
            if (isset($creator)) {
                $eventById->creator = $creator->username;
            }

            $booking = Booking::getInstance();
            $attendees = $booking->getBookingsByEventId($eventById->event_id);

            $this->view->attendees = $attendees;
            $this->view->event = $eventById;
        }

        $this->view->pageTitle = "Event Details";
    }
}
