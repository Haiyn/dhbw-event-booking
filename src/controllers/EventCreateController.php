<?php

namespace controllers;

use models\Event;
use models\User;

class EventCreateController extends Controller
{
    public function render($params)
    {
        session_start();
        if (
            isset($_POST["title"]) && isset($_POST["description"]) && isset($_POST["date"]) &&
            isset($_POST["visibility"])
        ) {
            $event_data = [
                "title" => trim(htmlspecialchars($_POST["title"])),
                "description" => trim(htmlspecialchars($_POST["description"])),
                "location" => trim(htmlspecialchars($_POST["location"])),
                "date" => htmlspecialchars($_POST["date"]),
                "time" => trim(htmlspecialchars($_POST["time"])),
                "visibility" => htmlspecialchars($_POST["visibility"]),
                "maximum_attendees" => filter_var(
                    htmlspecialchars($_POST["maximum_attendees"]),
                    FILTER_SANITIZE_NUMBER_INT
                ),
                "price" => filter_var(htmlspecialchars($_POST["price"]), FILTER_SANITIZE_NUMBER_INT)
            ];

            $this->createEvent($event_data);

            $this->setSuccess("Event successfully created.");
        }

        $this->view->pageTitle = "Create Event";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }

    /**
     * Validate the data of the event, throws an error if something is wrong
     * @param $data * Data of the event
     */
    private function validateData($data)
    {
        // Double check if all required fields have been set
        if (
            empty($data["title"]) || empty($data["description"]) ||
            empty($data["date"]) || empty($data["visibility"])
        ) {
            $this->setError("Please fill out all required fields.");
        }

        // Check if maxlength is exceeded
        if (strlen($data["title"]) > 32) {
            $this->setError("Length of title cannot exceed max length of 32.");
        }
        if (strlen($data["description"]) > 256) {
            $this->setError("Length of description cannot exceed max length of 256.");
        }
        if (strlen($data["location"]) > 32) {
            $this->setError("Length of location cannot exceed max length of 32.");
        }

        // Check if time is valid
        if (!empty($data['time']) && !preg_match("/^([0-1][0-9]|[2][0-3]):([0-5][0-9])$/", $data['time'])) {
            $this->setError("Please enter a valid time.");
        }

        // Check if date/ time is in the past
        if (strtotime($data["date"]) < strtotime(date("Y-m-d"))) {
            $this->setError("Please change the date to one not in the past.");
        } elseif (
            strtotime($data["date"]) === strtotime(date("Y-m-d")) && !empty($data["time"]) &&
            strtotime($data["time"]) < strtotime(date("H:i:s"))
        ) {
            $this->setError("Please change the time to one not in the past.");
        }

        // Check if maximum attendees is an valid int
        if (
            (!empty($new_data['maximum_attendees']) || $new_data['maximum_attendees'] === '0') &&
            filter_var($new_data['maximum_attendees'], FILTER_VALIDATE_INT)
        ) {
            // Check if maximum attendees is bigger than 0
            if ((int) $new_data['maximum_attendees'] < 1) {
                $this->setError("Please enter a number of maximum attendees that is at least 1!");
            }
        } else {
            $this->setError("Please enter a valid number of maximum attendees!");
        }

        // Check if price is a valid float
        if (!empty($data['price']) && !filter_var($data['price'], FILTER_VALIDATE_FLOAT)) {
            $this->setError("Please enter a valid price!");
        }

        // Check if price is 0 or bigger
        if (!empty($data['price']) && (int) $data['price'] < 0) {
            $this->setError("Please enter a price that is at least 0!");
        }
    }

    /**
     * Create the event after data validation
     * @param $data * Data of the event
     */
    private function createEvent($data)
    {
        if (isset($_SESSION["USER_ID"])) {
            $data["creator_id"] = $_SESSION["USER_ID"];
        }

        $this->validateData($data);

        $event = Event::getInstance();
        $event->addEvent($data);
    }
}
