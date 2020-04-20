<?php

namespace models;

use components\database\DatabaseService;

class Booking
{
    private static $instance;
    private static $database;

    public function __construct()
    {
        self::$instance = $this;
        self::$database = DatabaseService::newInstance(null);
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

    /**
     * Add the booking to the database
     * @param $data * Data of the booking
     * @return bool * Successful/ Not successful
     */
    public function addBooking($data)
    {
        return self::$database->execute(
            "INSERT INTO bookings VALUES (
            DEFAULT, :event_id, :user_id, :status, DEFAULT);",
            $this->mapBookingDataToBookingTableData($data)
        );
    }

    /**
     * Update the status of a booking
     * @param $event_id * If of the event
     * @param $user_id * Id of user to be updated
     * @param $status * New status of the booking
     * @return bool * Successful/ Not successful
     */
    public function updateBookingStatus($event_id, $user_id, $status)
    {
        return self::$database->execute(
            "UPDATE bookings
            SET status = :status
            WHERE event_id = :event_id AND user_id = :user_id;",
            [":event_id" => $event_id, ":user_id" => $user_id, ":status" => $status]
        );
    }

    /**
     * Delete a booking by event and user id
     * @param $event_id * Id of the event
     * @param $user_id * Id of the user
     * @return bool * Successful/ Not successful
     */
    public function deleteBookingByEventIdAndUserId($event_id, $user_id)
    {
        return self::$database->execute(
            "DELETE FROM bookings WHERE event_id = :event_id AND user_id = :user_id;",
            [":event_id" => $event_id, ":user_id" => $user_id]
        );
    }

    /**
     * Delete all bookings from the event, used when the event is being canceled
     * @param $event_id * Id of the event
     * @return bool * successful/not successful
     */
    public function deleteBookingsByEventId($event_id)
    {
        return self::$database->execute(
            "DELETE FROM bookings WHERE event_id = :event_id",
            [":event_id" => $event_id]
        );
    }

    /**
     * Maps the data to the database
     * @param $data * Data of the event
     * @return array * Modified data
     */
    private function mapBookingDataToBookingTableData($data)
    {
        return $data = [
            ":event_id" => $data['event_id'],
            ":user_id" => $data['user_id'],
            ":status" => $data['status'],
        ];
    }
}
