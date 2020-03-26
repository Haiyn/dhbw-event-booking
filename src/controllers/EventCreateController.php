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

            $this->validateData($event_data);

            $this->redirect('/event-overview');
        }

        $this->view->pageTitle = "Create Event";
        $this->view->isCreateEventError = isset($_GET["error"]);
    }

    private function validateData($data)
    {
        $event = new Event();

        if (!isset($data["title"]) || !is_string($data["title"])) {
            $this->redirect("/event-create?error");
        }
        if (!isset($data["description"]) || !is_string($data["description"])) {
            $this->redirect("/event-create?error");
        }
        if (!is_string($data["location"])) {
            $this->redirect("/event-create?error");
        }
        if (!isset($data["date"]) || !is_string($data["date"])) {
            $this->redirect("/event-create?error");
        }
        if (!is_string($data["time"])) {
            $this->redirect("/event-create?error");
        }
        if (!isset($data["visibility"]) || !is_string($data["visibility"])) {
            $this->redirect("/event-create?error");
        }
        if (!is_string($data["maximum_attendees"])) {
            $this->redirect("/event-create?error");
        }
        if (!is_numeric($data["price"])) {
            $this->redirect("/event-create?error");
        }

        $event->addEvent($data);
    }
}
