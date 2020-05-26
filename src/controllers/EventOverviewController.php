<?php

namespace controllers;

use models\Booking;
use models\enums\Visibility;
use models\Event;

/**
 * Class EventOverviewController
 * Controls the event overview and shows only public and invite only (if invited) events to the user.
 * @package controllers
 */
class EventOverviewController extends Controller
{
    public function render()
    {
        $this->session->checkSession();

        $event = Event::getInstance();
        $events = $event->getEvents();

        // Filter the events, so that only public events, or events where the current user
        // is invited to, are shown
        $filtered_events = [];
        $booking = Booking::getInstance();
        foreach ($events as $e) {
            // Check if event is invite only
            if ($e->visibility == Visibility::$INVITE_ONLY) {
                // Check if current user is event creator
                if ($e->creator_id == $_SESSION['USER_ID']) {
                    array_push($filtered_events, $e);
                    continue;
                }
                // Check if user is invited to an event
                foreach ($booking->getBookingsByEventId($e->event_id) as $b) {
                    if ($b->user_id == $_SESSION['USER_ID']) {
                        array_push($filtered_events, $e);
                        continue;
                    }
                }
            } else {
                array_push($filtered_events, $e);
            }
        }

        $this->view->pageTitle = "Event Overview";
        $this->view->events = $filtered_events;
    }
}
