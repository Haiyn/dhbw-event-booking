<?php

namespace controllers;

use components\core\Utility;
use components\email\EmailService;
use models\Booking;
use models\enums\Status;
use models\Event;
use models\User;

class EventDetailController extends Controller
{
    public function render($params)
    {
        session_start();
        if (isset($_GET['event_id'])) {
            $event = Event::getInstance();
            $eventById = $event->getEventById(trim(htmlspecialchars($_GET['event_id'])));
            $this->validateEventCreator($eventById);

            $booking = Booking::getInstance();
            $attendees = $booking->getBookingsByEventId($eventById->event_id);

            $this->view->attendees = $attendees;
            $this->view->event = $eventById;

            if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['date'])) {
                $event_data = [
                    "event_id" => trim(htmlspecialchars($_GET['event_id'])),
                    "title" => trim(htmlspecialchars($_POST['title'])),
                    "description" => trim(htmlspecialchars($_POST['description'])),
                    "location" => trim(htmlspecialchars($_POST['location'])),
                    "date" => htmlspecialchars($_POST['date']),
                    "time" => trim(htmlspecialchars($_POST['time'])),
                    "maximum_attendees" => filter_var(
                        htmlspecialchars($_POST['maximum_attendees']),
                        FILTER_SANITIZE_NUMBER_INT
                    ),
                    "price" => filter_var(htmlspecialchars($_POST["price"]), FILTER_SANITIZE_NUMBER_INT)
                ];

                $this->updateEvent($event_data, $eventById);

                $this->notifyAttendeesUpdated($attendees, $event_data);

                $this->setSuccess("Event successfully updated.", ["event_id" => $_GET['event_id']]);
            }
        }

        $this->view->pageTitle = "Event Details";

        if (!isset($this->view->isCreator)) {
            $this->view->isCreator = false;
        }
        if (!isset($this->view->edit)) {
            $this->view->edit = false;
        }

        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }

    /**
     * Validate the data of the event, throws an error if something is wrong
     * @param $new_data * New data of the event
     * @param $old_data * Old data of the event
     */
    private function validateData($new_data, $old_data)
    {
        // Double check if all required fields have been set
        if (empty($new_data["title"]) || empty($new_data["description"]) || empty($new_data["date"])) {
            $this->setError(
                "Please fill out all required fields.",
                ["event_id" => $_GET['event_id'], "edit" => ""]
            );
        }

        // Check if maxlength is exceeded
        if ($new_data['title'] !== $old_data->title && strlen($new_data["title"]) > 32) {
            $this->setError(
                "Length of title cannot exceed max length of 32.",
                ["event_id" => $_GET['event_id'], "edit" => ""]
            );
        }
        if ($new_data['description'] !== $old_data->description && strlen($new_data["description"]) > 256) {
            $this->setError(
                "Length of description cannot exceed max length of 256.",
                ["event_id" => $_GET['event_id'], "edit" => ""]
            );
        }
        if ($new_data['location'] !== $old_data->location && strlen($new_data["location"]) > 32) {
            $this->setError(
                "Length of location cannot exceed max length of 32.",
                ["event_id" => $_GET['event_id'], "edit" => ""]
            );
        }

        if ($new_data['time'] !== $old_data->time) {
            // Check if time is valid
            if (!empty($new_data['time']) && !preg_match("/^([0-1][0-9]|[2][0-3]):([0-5][0-9])$/", $new_data['time'])) {
                $this->setError("Please enter a valid time.", ["event_id" => $_GET['event_id'], "edit" => ""]);
            }

            // Check if date/ time is in the past
            if ($new_data['date'] !== $old_data->date) {
                if (strtotime($new_data["date"]) < strtotime(date("Y-m-d"))) {
                    $this->setError(
                        "Please change the date to one not in the past.",
                        ["event_id" => $_GET['event_id'], "edit" => ""]
                    );
                } elseif (
                    strtotime($new_data["date"]) === strtotime(date("Y-m-d")) && !empty($new_data["time"]) &&
                    strtotime($new_data["time"]) < strtotime(date("H:i:s"))
                ) {
                    $this->setError(
                        "Please change the time to one not in the past.",
                        ["event_id" => $_GET['event_id'], "edit" => ""]
                    );
                }
            }
        }

        if ($new_data['maximum_attendees'] !== $old_data->maximum_attendees) {
            // Check if maximum attendees is an valid int
            if (
                (!empty($new_data['maximum_attendees']) || $new_data['maximum_attendees'] === '0') &&
                filter_var($new_data['maximum_attendees'], FILTER_VALIDATE_INT)
            ) {
                // Check if maximum attendees is bigger than 0
                if ((int) $new_data['maximum_attendees'] < 1) {
                    $this->setError(
                        "Please enter a number of maximum attendees that is at least 1!",
                        ["event_id" => $_GET['event_id'], "edit" => ""]
                    );
                }
            } elseif (!empty($new_data['maximum_attendees']) || $new_data['maximum_attendees'] === '0') {
                $this->setError(
                    "Please enter a valid number of maximum attendees!",
                    ["event_id" => $_GET['event_id'], "edit" => ""]
                );
            }

            if (!empty($new_data['maximum_attendees']) || $new_data['maximum_attendees'] === '0') {
                // Check if new number of maximum attendees is lower than the current amount of maximum attendees
                $booking = Booking::getInstance();
                $bookings = $booking->getBookingsByEventId($old_data->event_id);
                if (sizeof($bookings) > $new_data['maximum_attendees']) {
                    $this->setError(
                        "New number of maximum attendees cannot be lower than the current number of attendees!",
                        ["event_id" => $_GET['event_id'], "edit" => ""]
                    );
                }
            }
        }

        if ($new_data['price'] !== $old_data->price) {
            // Check if price is a valid float
            if (!empty($new_data['price']) && !filter_var($new_data['price'], FILTER_VALIDATE_FLOAT)) {
                $this->setError("Please enter a valid price!", "&event_id={$_GET['event_id']}&edit");
            }

            // Check if price is 0 or bigger
            if (!empty($new_data['price']) && (int) $new_data['price'] < 0) {
                $this->setError(
                    "Please enter a price that is at least 0!",
                    ["event_id" => $_GET['event_id'], "edit" => ""]
                );
            }
        }
    }

    /**
     * Update the event after data validation
     * @param $new_data * New data of the event
     * @param $old_data * Old data of the event
     */
    private function updateEvent($new_data, $old_data)
    {
        $user = User::getInstance();

        if (isset($_SESSION["USER_ID"])) {
            $data["creator_id"] = $user->getUserById($_SESSION["USER_ID"]);
        }

        $this->validateData($new_data, $old_data);

        $event = Event::getInstance();
        $event->updateEvent($new_data);
    }

    /**
     * Validate if the current user is the creator of this event in order to enable the editing option
     * @param $event * This event
     */
    private function validateEventCreator($event)
    {
        if (isset($event->creator_id)) {
            $user = User::getInstance();
            $creator = $user->getUserById($event->creator_id);

            if (isset($creator)) {
                $event->creator = $creator->username;
            }

            $current_user_id = $_SESSION['USER_ID'];
            $current_user = $user->getUserById($current_user_id);

            // Check if the current user is the same as the creator
            if (isset($current_user) && isset($creator) && $current_user->user_id === $creator->user_id) {
                $this->view->isCreator = true;

                // Check if editing is enabled, if so, enable it
                if (isset($_GET['edit'])) {
                    $this->view->edit = true;

                    // Delete attendee if delete_attendee is set
                    if (isset($_GET['delete_attendee'])) {
                        $booking = Booking::getInstance();
                        $booking->deleteBookingByEventIdAndUserId($_GET['event_id'], $_GET['delete_attendee']);
                        $this->notifyAttendeeRemoved($event, $_GET['delete_attendee']);
                    }
                }
            }
        }
    }

    /**
     * Notify attendee that he has been removed from the event
     * @param $event * This event
     * @param $attendee_id * Id of attendee to be notified
     */
    private function notifyAttendeeRemoved($event, $attendee_id)
    {
        // Check if Email Sending is enabled
        $initFile = Utility::getIniFile();
        $user = User::getInstance();
        $attendee = $user->getUserById($attendee_id);
        if (filter_var($initFile['EMAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            // Send the notification email to the email address
            $emailService = EmailService::getInstance();
            $emailService->sendEmail(
                $attendee->email,
                "You have been removed from an event",
                "You have been removed from the event with the title '{$event->title}'.<br/>
                Use this <a href='{$initFile['URL']}/event-detail?event_id={$event->event_id}'> link</a>
                to view the event."
            );
        } else {
            $this->setError(
                "An internal error occurred, notifications could not be sent!",
                ["event_id" => $_GET['event_id']]
            );
        }
    }

    /**
     * Notify all attendees of this event that it has been updated
     * @param $attendees * Attendees of the event
     * @param $event_data * Data of the event
     */
    private function notifyAttendeesUpdated($attendees, $event_data)
    {
        if (isset($_POST['notify']) && filter_var($_POST['notify'], FILTER_VALIDATE_BOOLEAN)) {
            // Check if Email Sending is enabled
            $initFile = Utility::getIniFile();
            if (filter_var($initFile['EMAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
                // Iterate through each attendee
                foreach ($attendees as $attendee) {
                    if ($attendee->status == Status::$ACCEPTED) {
                        // Send the notification email to the email address
                        $emailService = EmailService::getInstance();
                        $emailService->sendEmail(
                            $attendee->email,
                            "An event you are attending has been updated",
                            "The event with title '{$event_data['title']}' has been updated by the creator.<br/>
                            Use this <a href='{$initFile['URL']}/event-detail?event_id={$event_data['event_id']}'>
                            link</a> to view the event."
                        );
                    }
                }
            } else {
                $this->setError(
                    "An internal error occurred, notifications could not be sent!",
                    ["event_id" => $_GET['event_id']]
                );
            }
        }
    }
}
