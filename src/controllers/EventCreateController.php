<?php

namespace controllers;

use models\Event;

class EventCreateController extends Controller
{

    public function render($params)
    {
        if (isset($_POST["title"]) && isset($_POST["description"]) && isset($_POST["location"])) {
            $event_data = [
                "title" => htmlspecialchars($_POST["title"]),
                "password" => htmlspecialchars($_POST["description"]),
                "location" => htmlspecialchars($_POST["location"]),
                "date" => htmlspecialchars($_POST["date"]),
                "time" => htmlspecialchars($_POST["time"]),
                "visibility" => htmlspecialchars($_POST["visibility"]),
                "maximum_attendees" => htmlspecialchars($_POST["maximum_attendees"]),
                "price" => htmlspecialchars($_POST["price"])
            ];

            $this->_validateData($event_data);

            $this->redirect('/event-overview');
        }

        $this->view->pageTitle = "Create Event";
        $this->view->isCreateEventError = isset($_GET["error"]);
    }

    private function validateData($data)
    {
        $event = new Event();

        $event->addEvent($data);
    }
}
