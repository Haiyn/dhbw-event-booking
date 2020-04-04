<?php

namespace controllers;

use models\Booking;
use models\Event;
use models\User;

class EventDetailController extends Controller
{
    public function render($params)
    {
        session_start();
        $_SESSION['USER_ID'] = "b89fe465-b4cf-4a3d-a006-19951bc31ae4";
        if (isset($_GET['event_id'])) {
            $event = Event::getInstance();
            $eventById = $event->getEventById(htmlspecialchars($_GET['event_id']));

            $user = User::getInstance();
            if (isset($eventById->creator_id)) {
                $creator = $user->getUserById($eventById->creator_id);

                if (isset($creator)) {
                    $eventById->creator = $creator->username;
                }

                $current_user_id = $_SESSION['USER_ID'];
                $current_user = $user->getUserById($current_user_id);

                if (isset($current_user) && isset($creator) && $current_user->user_id === $creator->user_id) {
                    $this->view->isCreator = true;

                    if (isset($_GET['edit'])) {
                        $edit = $_GET['edit'];

                        if ($edit) {
                            $this->view->isReadonly = false;
                        }
                    }
                }
            }

            $booking = Booking::getInstance();
            $attendees = $booking->getBookingsByEventId($eventById->event_id);

            $this->view->attendees = $attendees;
            $this->view->event = $eventById;
        }

        $this->view->pageTitle = "Event Details";

        if (!isset($this->view->isCreator)) {
            $this->view->isCreator = false;
        }
        if (!isset($this->view->isReadonly)) {
            $this->view->isReadonly = true;
        }

        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }
}
