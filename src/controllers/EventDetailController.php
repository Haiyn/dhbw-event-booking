<?php

namespace controllers;

use components\core\Utility;
use components\core\ValidatorException;
use components\email\EmailService;
use components\validators\EventValidator;
use models\Booking;
use models\enums\Status;
use models\Event;
use models\User;

/**
 * Class EventDetailController
 * Controls everything in the event detail view: displaying and editing event data
 * @package controllers
 */
class EventDetailController extends Controller
{
    public function render($params)
    {
        $this->session->checkSession();

        if (isset($_GET['event_id'])) {
            $event = Event::getInstance();
            $eventById = $event->getEventById(trim(htmlspecialchars($_GET['event_id'])));
            // Check if event with id exists, if not, redirect to event overview
            if (empty($eventById)) {
                $this->redirect("event-overview");
            }
            $this->validateEventCreator($eventById);

            // At this point the current user is the event creator

            $booking = Booking::getInstance();
            $attendees = $booking->getBookingsByEventId($eventById->event_id);

            foreach ($attendees as $attendee) {
                if ($attendee->user_id == $_SESSION['USER_ID']) {
                    $this->view->status = $attendee->status;
                }
            }

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

            if (isset($_GET['book_event'])) {
                $this->attendEvent($eventById, $attendees, $_SESSION['USER_ID']);

                $this->setSuccess(
                    "You have been successfully added to this event.",
                    ["event_id" => $_GET['event_id']]
                );
            }

            if (isset($_GET['unbook_event'])) {
                $this->unattendEvent($eventById, $attendees, $_SESSION['USER_ID']);

                $this->setSuccess(
                    "You have been successfully removed from this event.",
                    ["event_id" => $_GET['event_id']]
                );
            }

            if (isset($_POST['invite_user']) && isset($_POST['invite_user_name'])) {
                $this->inviteUserToEvent($eventById, $_POST['invite_user_name'], $attendees);

                $this->setSuccess(
                    "User successfully invited to the event.",
                    ["event_id" => $_GET['event_id']]
                );
            }

            if (isset($_POST['cancel_event']) && isset($_POST['reason'])) {
                $this->cancelEvent($eventById, $attendees, $_POST['reason']);

                $this->setSuccess(
                    "The event has been successfully deleted.",
                    ["event_id" => $_GET['event_id']]
                );
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
        $this->view->isWarning = isset($_GET["warning"]);
        $this->view->isError = isset($_GET["error"]);
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

        $event_validator = EventValidator::getInstance();
        try {
            $event_validator->validateEventEditData($new_data, $old_data);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage(), $exception->getParams());
        }

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
                $event->creator_username = $creator->username;
                $event->creator_email = $creator->email;
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
                        if (!$booking->deleteBookingByEventIdAndUserId($_GET['event_id'], $_GET['delete_attendee'])) {
                            // Something went wrong while deleting the bookings
                            $this->setError("An error occurred while deleting the attendees from the event.", []);
                        }
                        $successful = $this->notifyAttendee(
                            $_GET['delete_attendee'],
                            "You have been removed from an event",
                            "You have been removed from the event with the title '{$event->title}'.<br/>
                            Use this <a href='" . Utility::getApplicationURL() . "/event-detail?event_id=
                            {$event->event_id}'>link</a>
                            to view the event."
                        );
                        if (!$successful) {
                            $this->setWarning(
                                "Attendee successfully deleted. Emails are disabled. Attendee was not
                                notified of the change.",
                                ["event_id" => $_GET['event_id']]
                            );
                        } else {
                            $this->setSuccess(
                                "Attendee successfully deleted.",
                                ["event_id" => $_GET['event_id']]
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Notify attendee that he has been removed from the event. Used when a attendee
     * has been removed from the list or the event has been canceled.
     * @param $attendee_id * Id of attendee to be notified
     * @param $subject * Subject of the email
     * @param $message * Message of the email
     * @return bool * Successful/ not successful
     */
    private function notifyAttendee($attendee_id, $subject, $message)
    {
        // Check if Email Sending is enabled
        $initFile = Utility::getIniFile();
        $user = User::getInstance();
        $attendee = $user->getUserById($attendee_id);
        if (filter_var($initFile['EMAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            // Send the notification email to the email address
            $emailService = EmailService::getInstance();
            $emailService->sendEmail($attendee->email, $subject, $message);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Notify all attendees of this event that it has been updated. Used when
     * any of the event data has been updated (excluding the attendees list)
     * and the creator clicks on update event.
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
                            Use this <a href='" . Utility::getApplicationURL() . "/event-detail?event_id=
                            {$event_data['event_id']}'>link</a> to view the event."
                        );
                    }
                }
            } else {
                $this->setWarning(
                    "Event successfully updated. But emails are disabled. Users were not notified of the change.",
                    ["event_id" => $_GET['event_id']]
                );
            }
        }
    }

    /**
     * Create a booking and add the attendee to the event
     * @param $event * This event
     * @param $attendees * Current attendees of this event
     * @param $attendee_id * Id of attendee to be added
     */
    private function attendEvent($event, $attendees, $attendee_id)
    {
        // Validate the data
        $event_validator = EventValidator::getInstance();
        $status = null;
        try {
            $status = $event_validator->validateAttendData($event, $attendees, $attendee_id);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage(), $exception->getParams());
        }
        // Add the attendee, based on the current status
        $booking = Booking::getInstance();
        if ($status == Status::$INVITED) {
            if (!$booking->updateBookingStatus($event->event_id, $attendee_id, Status::$ACCEPTED)) {
                // Something went wrong while updating the booking
                $this->setError("An error occurred while attending to the event.", []);
            }
        } else {
            if (
                !$booking->addBooking(["event_id" => $event->event_id, "user_id" => $attendee_id,
                "status" => Status::$ACCEPTED])
            ) {
                // Something went wrong while adding the booking
                $this->setError("An error occurred while attending to the event.", []);
            }
        }
        // Notify attendee
        $successful = $this->notifyAttendee(
            $attendee_id,
            "You have been added to an event",
            "You have been added to the event with the title '{$event->title}'.<br/>
            Use this <a href='" . Utility::getApplicationURL() . "/event-detail?event_id={$event->event_id}'>
            link</a> to view the event."
        );
        if (!$successful) {
            $this->setWarning(
                "Attendee successfully added. Emails are disabled. Attendee was not
                                notified of the change.",
                ["event_id" => $_GET['event_id']]
            );
        }
    }

    /**
     * Invite a user to the event (can only be done by the creator)
     * @param $event * This event
     * @param $user * User to be invited, is either username or email
     * @param $attendees * Attendees of this event
     */
    private function inviteUserToEvent($event, $user, $attendees)
    {
        // Get the user
        $u = User::getInstance();
        $found_user = $u->getUserByEmail($user);
        $found_user = empty($found_user) ? $u->getUserByUsername($user) : $found_user;

        // Validate the user
        $event_validator = EventValidator::getInstance();
        try {
            $event_validator->validateInviteUserData($event, $found_user, $attendees);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage(), $exception->getParams());
        }

        // Add the user to the attendees list
        $booking = Booking::getInstance();
        if (
            !$booking->addBooking(["event_id" => $event->event_id, "user_id" => $found_user->user_id,
            "status" => Status::$INVITED])
        ) {
            // Something went wrong while adding the booking
            $this->setError("An error occurred while adding the attendee to the event.", []);
        }
        $successful = $this->notifyAttendee(
            $found_user->user_id,
            "You have been invited to an event",
            "You have been invited to the event with the title '{$event->title}'. Use this
            <a href='" . Utility::getApplicationURL() . "/event-detail?event_id={$event->event_id}'>
            link</a> to view the event."
        );
        if (!$successful) {
            $this->setWarning(
                "Attendee successfully invited. Emails are disabled. Attendee was not notified.",
                ["event_id" => $_GET['event_id'], "edit" => ""]
            );
        }
    }

    /**
     * Delete a booking and remove the attendee from the event
     * @param $event * This event
     * @param $attendees * Current attendees of this event
     * @param $attendee_id * Id of attendee to be removed
     */
    private function unattendEvent($event, $attendees, $attendee_id)
    {
        $event_validator = EventValidator::getInstance();
        try {
            $event_validator->validateUnattendData($attendees, $attendee_id);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage(), $exception->getParams());
        }

        $booking = Booking::getInstance();
        if (!$booking->deleteBookingByEventIdAndUserId($event->event_id, $attendee_id)) {
            // Something went wrong while deleting the bookings
            $this->setError("An error occurred while deleting the attendees from the event.", []);
        }
        $successful = $this->notifyAttendee(
            $attendee_id,
            "You have been removed from an event",
            "You have been removed from the event with the title '{$event->title}'.<br/>
            Use this <a href='" . Utility::getApplicationURL() . "/event-detail?event_id={$event->event_id}'>
            link</a> to view the event."
        );
        if (!$successful) {
            $this->setWarning(
                "Attendee successfully removed. Emails are disabled. Attendee was not
                notified of the change.",
                ["event_id" => $_GET['event_id']]
            );
        }
    }

    /**
     * Cancel an event, delete all bookings and the event from the database
     * @param $event * Event to be canceled
     * @param $attendees * Attendees of this event
     * @param $reason * Reason of the cancellation
     */
    private function cancelEvent($event, $attendees, $reason)
    {
        // Delete all bookings
        $booking = Booking::getInstance();
        if (!$booking->deleteBookingsByEventId($event->event_id)) {
            // Something went wrong while deleting existing bookings
            $this->setError("An error occurred while deleting all attendees of this event.", []);
        }
        // Delete the event
        $e = Event::getInstance();
        if (!$e->deleteEventById($event->event_id)) {
            // Something went wrong while deleting the existing event
            $this->setError("An error occurred while deleting the event.", []);
        }
        // Notify all attendees
        foreach ($attendees as $attendee) {
            $successful = $this->notifyAttendee(
                $attendee->user_id,
                "An event has been canceled",
                "The event with the title '{$event->title}' has been canceled by the creator,<br/>
                so you have been removed from the event.<br/>
                Reason/ Message:<br/>
                {$reason}"
            );
            if (!$successful) {
                $this->setWarning(
                    "Event successfully deleted. Emails are disabled. Attendees were not notified.",
                    ["event_id" => $_GET['event_id']]
                );
                break;
            }
        }
    }
}
