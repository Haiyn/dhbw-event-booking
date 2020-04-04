<?php

namespace models;

use components\database\Database;

class Event
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
     * Add the event to the database
     * @param $data * Data of the event
     */
    public function addEvent($data)
    {
        self::$database->execute(
            "INSERT INTO events VALUES (
            DEFAULT, :creator_id, DEFAULT, :title, :description, :location, :date, :time,
                  :visibility, :maximum_attendees, :price);",
            $this->mapEventDataToEventTableData($data)
        );
    }

    /**
     * Update an event of the database
     * @param $data * Data of the event
     */
    public function updateEvent($data)
    {
        self::$database->execute(
            "UPDATE events
            SET title = :title, description = :description, location = :location, date = :date, time = :time,
                  maximum_attendees = :maximum_attendees, price = :price
            WHERE event_id = :event_id;",
            $this->mapUpdatedEventDataToEventTableData($data)
        );
    }

    /**
     * Get all events
     * @return array * Array of events
     */
    public function getEvents()
    {
        $events = self::$database->fetch(
            "SELECT * FROM events",
            []
        );
        if (empty($events)) {
            return [];
        }
        return $events;
    }

    /**
     * Get event by id
     * @param $event_id * Id of the event
     * @return array * Array with found events, returning the first result
     */
    public function getEventById($event_id)
    {
        $events = self::$database->fetch(
            "SELECT * FROM events WHERE event_id = :event_id",
            [":event_id" => $event_id]
        );
        if (empty($events)) {
            return [];
        }
        return $events[0];
    }

    /**
     * Maps the data to the database
     * @param $data * Data of the event
     * @return array * Modified data
     */
    private function mapEventDataToEventTableData($data)
    {
        // Check for empty values, postgres must receive null not ""
        if (empty($data['location'])) {
            $data['location'] = null;
        }
        if (empty($data['time'])) {
            $data['time'] = null;
        }
        if (empty($data['maximum_attendees'])) {
            $data['maximum_attendees'] = null;
        }
        if (empty($data['price'])) {
            $data['price'] = null;
        }

        return $data = [
            ":title" => $data['title'],
            ":description" => $data['description'],
            ":location" => $data['location'],
            ":date" => $data['date'],
            ":time" => $data['time'],
            ":visibility" => $data['visibility'],
            ":maximum_attendees" => $data['maximum_attendees'],
            ":price" => $data['price'],
        ];
    }

    /**
     * Maps the data to the database
     * @param $data * Data of the event
     * @return array * Modified data
     */
    private function mapUpdatedEventDataToEventTableData($data)
    {
        // Check for empty values, postgres must receive null not ""
        if (empty($data['location'])) {
            $data['location'] = null;
        }
        if (empty($data['time'])) {
            $data['time'] = null;
        }
        if (empty($data['maximum_attendees'])) {
            $data['maximum_attendees'] = null;
        }
        if (empty($data['price'])) {
            $data['price'] = null;
        }

        return $data = [
            ":event_id" => $data['event_id'],
            ":title" => $data['title'],
            ":description" => $data['description'],
            ":location" => $data['location'],
            ":date" => $data['date'],
            ":time" => $data['time'],
            ":maximum_attendees" => $data['maximum_attendees'],
            ":price" => $data['price'],
        ];
    }
}
