<?php

namespace controllers;

use models\Event;
use models\User;

class EventCreateController extends Controller
{
    public function render($params)
    {
        session_start();
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

            $this->validateData($event_data);

            $this->createEvent($event_data);

            $this->setSuccess("Event successfully created.");
        }

        $this->view->pageTitle = "Create Event";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }

    /**
     * Validate the teh data of the event, throws an error if something is wrong
     * @param $data * Data of the event
     */
    private function validateData($data)
    {
        // Double check if all required fields have been set
        if (
            !isset($data["title"]) || !isset($data["description"]) ||
            !isset($data["date"]) || !isset($data["visibility"])
        ) {
            $this->setError("Required fields must be set.");
        }

        // Check if maximum attendees is an valid int
        if (!empty($data['maximum_attendees']) && !filter_var($data['maximum_attendees'], FILTER_VALIDATE_INT)) {
            $this->setError("Please enter a valid number!");
        }

        // Check if price is a valid float
        if (!empty($data['price']) && !filter_var($data['price'], FILTER_VALIDATE_FLOAT)) {
            $this->setError("Please enter a valid price!");
        }
    }

    /**
     * Create the event after data validation
     * @param $data * Data of the event
     */
    private function createEvent($data)
    {
        $user = User::newInstance();

        if (isset($_SESSION["USER_ID"])) {
            $data["creator_id"] = $user->getUserById($_SESSION["USER_ID"]);
        }

        $this->validateData($data);

        $event = Event::newInstance();
        $event->addEvent($data);
    }
}
