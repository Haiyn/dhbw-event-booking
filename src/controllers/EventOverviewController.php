<?php

namespace controllers;

use models\Event;

class EventOverviewController extends Controller
{
    public function render($params)
    {
        session_start();

        $event = Event::getInstance();
        $events = $event->getEvents();

        $this->view->pageTitle = "Event Overview";
        $this->view->events = $events;
    }
}
