<?php

namespace components\validators;

use components\core\ControllerException;
use models\Booking;
use models\enums\Visibility;

class EventValidator
{
    private static $instance;

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Validate the data of the event when being created, throws an error if something is wrong
     * @param $data * Data of the event
     * @throws ControllerException
     */
    public function validateEventCreateData($data)
    {
        // Double check if all required fields have been set
        if (
            empty($data["title"]) || empty($data["description"]) ||
            empty($data["date"]) || empty($data["visibility"])
        ) {
            throw new ControllerException("Please fill out all required fields.");
        }

        // Check if maxlength is exceeded
        if (strlen($data["title"]) > 32) {
            throw new ControllerException("Length of title cannot exceed max length of 32.");
        }
        if (strlen($data["description"]) > 256) {
            throw new ControllerException("Length of description cannot exceed max length of 256.");
        }
        if (strlen($data["location"]) > 32) {
            throw new ControllerException("Length of location cannot exceed max length of 32.");
        }

        // Check if time is valid
        if (!empty($data['time']) && !preg_match("/^([0-1][0-9]|[2][0-3]):([0-5][0-9])$/", $data['time'])) {
            throw new ControllerException("Please enter a valid time.");
        }

        // Check if date/ time is in the past
        if (strtotime($data["date"]) < strtotime(date("Y-m-d"))) {
            throw new ControllerException("Please change the date to one not in the past.");
        } elseif (
            strtotime($data["date"]) === strtotime(date("Y-m-d")) && !empty($data["time"]) &&
            strtotime($data["time"]) < strtotime(date("H:i:s"))
        ) {
            throw new ControllerException("Please change the time to one not in the past.");
        }

        // Check if maximum attendees is an valid int
        if (
            (!empty($data['maximum_attendees']) || $data['maximum_attendees'] === '0') &&
            filter_var($data['maximum_attendees'], FILTER_VALIDATE_INT)
        ) {
            // Check if maximum attendees is bigger than 0
            if ((int) $data['maximum_attendees'] < 1) {
                throw new ControllerException("Please enter a number of maximum attendees that is at least 1!");
            }
        } elseif (!empty($data['maximum_attendees']) || $data['maximum_attendees'] === '0') {
            throw new ControllerException("Please enter a valid number of maximum attendees!");
        }

        // Check if price is a valid float
        if (!empty($data['price']) && !filter_var($data['price'], FILTER_VALIDATE_FLOAT)) {
            throw new ControllerException("Please enter a valid price!");
        }

        // Check if price is 0 or bigger
        if (!empty($data['price']) && (int) $data['price'] < 0) {
            throw new ControllerException("Please enter a price that is at least 0!");
        }
    }

    /**
     * Validate the data of the event when being edited, throws an error if something is wrong
     * @param $new_data * New data of the event
     * @param $old_data * Old data of the event
     * @throws ControllerException
     */
    public function validateEventEditData($new_data, $old_data)
    {
        // Double check if all required fields have been set
        if (empty($new_data["title"]) || empty($new_data["description"]) || empty($new_data["date"])) {
            throw new ControllerException(
                "Please fill out all required fields.",
                ["event_id" => $_GET['event_id'], "edit" => ""]
            );
        }

        // Check if maxlength is exceeded
        if ($new_data['title'] !== $old_data->title && strlen($new_data["title"]) > 32) {
            throw new ControllerException(
                "Length of title cannot exceed max length of 32.",
                ["event_id" => $_GET['event_id'], "edit" => ""]
            );
        }
        if ($new_data['description'] !== $old_data->description && strlen($new_data["description"]) > 256) {
            throw new ControllerException(
                "Length of description cannot exceed max length of 256.",
                ["event_id" => $_GET['event_id'], "edit" => ""]
            );
        }
        if ($new_data['location'] !== $old_data->location && strlen($new_data["location"]) > 32) {
            throw new ControllerException(
                "Length of location cannot exceed max length of 32.",
                ["event_id" => $_GET['event_id'], "edit" => ""]
            );
        }

        if ($new_data['time'] !== $old_data->time) {
            // Check if time is valid
            if (!empty($new_data['time']) && !preg_match("/^([0-1][0-9]|[2][0-3]):([0-5][0-9])$/", $new_data['time'])) {
                throw new ControllerException(
                    "Please enter a valid time.",
                    ["event_id" => $_GET['event_id'], "edit" => ""]
                );
            }

            // Check if date/ time is in the past
            if ($new_data['date'] !== $old_data->date) {
                if (strtotime($new_data["date"]) < strtotime(date("Y-m-d"))) {
                    throw new ControllerException(
                        "Please change the date to one not in the past.",
                        ["event_id" => $_GET['event_id'], "edit" => ""]
                    );
                } elseif (
                    strtotime($new_data["date"]) === strtotime(date("Y-m-d")) && !empty($new_data["time"]) &&
                    strtotime($new_data["time"]) < strtotime(date("H:i:s"))
                ) {
                    throw new ControllerException(
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
                    throw new ControllerException(
                        "Please enter a number of maximum attendees that is at least 1!",
                        ["event_id" => $_GET['event_id'], "edit" => ""]
                    );
                }
            } elseif (!empty($new_data['maximum_attendees']) || $new_data['maximum_attendees'] === '0') {
                throw new ControllerException(
                    "Please enter a valid number of maximum attendees!",
                    ["event_id" => $_GET['event_id'], "edit" => ""]
                );
            }

            if (!empty($new_data['maximum_attendees']) || $new_data['maximum_attendees'] === '0') {
                // Check if new number of maximum attendees is lower than the current amount of maximum attendees
                $booking = Booking::getInstance();
                $bookings = $booking->getBookingsByEventId($old_data->event_id);
                if (sizeof($bookings) > $new_data['maximum_attendees']) {
                    throw new ControllerException(
                        "New number of maximum attendees cannot be lower than the current number of attendees!",
                        ["event_id" => $_GET['event_id'], "edit" => ""]
                    );
                }
            }
        }

        if ($new_data['price'] !== $old_data->price) {
            // Check if price is a valid float
            if (!empty($new_data['price']) && !filter_var($new_data['price'], FILTER_VALIDATE_FLOAT)) {
                throw new ControllerException("Please enter a valid price!", "&event_id={$_GET['event_id']}&edit");
            }

            // Check if price is 0 or bigger
            if (!empty($new_data['price']) && (int) $new_data['price'] < 0) {
                throw new ControllerException(
                    "Please enter a price that is at least 0!",
                    ["event_id" => $_GET['event_id'], "edit" => ""]
                );
            }
        }
    }

    /**
     * Validate the data when the user tries to attend to the event
     * @param $event * This event
     * @param $attendees * Current attendees of this event
     * @param $attendee_id * Id of the attendee
     * @throws ControllerException
     */
    public function validateAttendData($event, $attendees, $attendee_id)
    {
        // Check if current user is the same as the user to be added
        if ($attendee_id != $_SESSION['USER_ID']) {
            throw new ControllerException(
                "You cannot add others to the event!",
                ["event_id" => $_GET['event_id']]
            );
        }
        // Check if event is invite only
        if ($event->visibility != Visibility::$PUBLIC) {
            throw new ControllerException(
                "Cannot attend to this event, because it is invite only!",
                ["event_id" => $_GET['event_id']]
            );
        }
        // Check if event is full
        if (!empty($event->maximum_attendees) && count($attendees) >= $event->maximum_attendees) {
            throw new ControllerException(
                "Cannot attend to this event, because it is full!",
                ["event_id" => $_GET['event_id']]
            );
        }
        // Check if user is already attending to this event
        foreach ($attendees as $attendee) {
            if ($attendee->user_id == $attendee_id) {
                throw new ControllerException(
                    "Cannot attend to this event, because you are already attending to it!",
                    ["event_id" => $_GET['event_id']]
                );
            }
        }
    }

    /**
     * Validate the data when the user tries to attend to the event
     * @param $attendees * Current attendees of this event
     * @param $attendee_id * Id of the attendee
     * @throws ControllerException
     */
    public function validateUnattendData($attendees, $attendee_id)
    {
        // Check if user is already attending to this event
        $attending = false;
        foreach ($attendees as $attendee) {
            if ($attendee->user_id == $attendee_id) {
                $attending = true;
                break;
            }
        }
        if (!$attending) {
            throw new ControllerException(
                "Cannot be removed from the event, because you are not attending to it!",
                ["event_id" => $_GET['event_id']]
            );
        }
    }
}
