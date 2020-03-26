<?php

namespace controllers;

use models\enums\Visibility;
use models\Event;
use models\User;

class EventCreateController extends Controller
{

    public function render($params)
    {
        if (isset($_POST["title"]) && isset($_POST["description"]) && isset($_POST["location"])) {
            $event_data = [
                "title" => htmlspecialchars($_POST["title"]),
                "description" => htmlspecialchars($_POST["description"]),
                "location" => htmlspecialchars($_POST["location"]),
                "date" => htmlspecialchars($_POST["date"]),
                "time" => htmlspecialchars($_POST["time"]),
                "visibility" => htmlspecialchars($_POST["visibility"]),
                "maximum_attendees" => htmlspecialchars($_POST["maximum_attendees"]),
                "price" => htmlspecialchars($_POST["price"])
            ];

            $event = new Event();
            $event->addEvent($this->validateData($event_data));

            //$this->_redirect('/event-overview');
        }

        $this->_view->pageTitle = "Create Event";
        $this->_view->isCreateEventError = isset($_GET["error"]);
    }

    private function validateData($data)
    {
        if (
            !isset($data["title"]) || !isset($data["description"]) ||
            !isset($data["date"]) || !isset($data["visibility"])
        ) {
            $this->_setError("Required fields must be set.");
            return null;
        }

        $user = User::newInstance();

        if (isset($_SESSION["USER_ID"])) {
            $data["creator_id"] = $user->getUserById($_SESSION["USER_ID"]);
        }
        return $data;
    }
}
