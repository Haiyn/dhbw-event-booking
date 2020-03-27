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
            "SELECT users.username, bookings.status FROM bookings
            INNER JOIN users ON bookings.user_id = users.user_id WHERE event_id = :event_id",
            [":event_id" => $event_id]
        );
    }
}
