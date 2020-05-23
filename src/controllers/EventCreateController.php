<?php

namespace controllers;

use components\validators\ValidatorException;
use components\validators\EventValidator;
use models\Event;

/**
 * Class EventCreateController
 * Manages the creation of new events.
 * @package controllers
 */
class EventCreateController extends Controller
{
    public function render()
    {
        $this->session->checkSession();

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
     * Create the event after data validation
     * @param $data * Data of the event
     */
    private function createEvent($data)
    {
        if (isset($_SESSION["USER_ID"])) {
            $data["creator_id"] = $_SESSION["USER_ID"];
        }

        $event_validator = EventValidator::getInstance();
        try {
            $event_validator->validateEventCreateData($data);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage(), $exception->getParams());
        }

        $event = Event::getInstance();
        $event->addEvent($data);
    }
}
