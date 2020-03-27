<?php

namespace controllers;

use models\Event;
use models\User;

class EventDetailController extends Controller
{
    public function render($params)
    {
        session_start();
        if (isset($_GET['event_id'])) {
            $event = Event::getInstance();
            $eventById = $event->getEventById(htmlspecialchars($_GET['event_id']));

            $user = User::newInstance();
            $creator = $user->getUserById($eventById->creator_id);
            if (empty($creator)) {
                $creator = "";
            }
            $eventById->creator = $creator;

            $this->view->event = $eventById;
        }

        $this->view->pageTitle = "Event Details";
    }
}