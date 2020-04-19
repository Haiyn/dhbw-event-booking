<?php

namespace models;

use components\database\Database;

class Booking
{
    private static $instance;
    private static $database;

    public function __construct()
    {
        self::$instance = $this;
        self::$database = Database::newInstance(null);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get all bookings of an event
     * @param $event_id * Id of the event
     * @return array * Array of found bookings
     */
    public function getBookingsByEventId($event_id)
    {
        return self::$database->fetch(
            "SELECT users.username, users.email, bookings.user_id, bookings.status FROM bookings
            INNER JOIN users ON bookings.user_id = users.user_id WHERE event_id = :event_id",
            [":event_id" => $event_id]
        );
    }



    public function getBookingsByUserId($user_id)
    {
        return self::$database->fetch(
            "SELECT users.username, users.email, bookings.user_id FROM bookings
            INNER JOIN users ON bookings.user_id = users.user_id WHERE user_id = :user_id",
            [":event_id" => $event_id]
        );
    }


    /**
     * Delete a booking by event and user id
     * @param $event_id * Id of the event
     * @param $user_id * Id of the user
     */
    public function deleteBookingByEventIdAndUserId($event_id, $user_id)
    {
        self::$database->execute(
            "DELETE FROM bookings WHERE event_id = :event_id AND user_id = :user_id;",
            [":event_id" => $event_id, ":user_id" => $user_id]
        );
    }
}
