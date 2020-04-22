<?php

namespace models;

use components\database\DatabaseService;

/**
 * Class Event
 * Database model for the events table. Includes all needed queries.
 * @package models
 */
class Event
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
     * Add the event to the database
     * @param $data * Data of the event
     */
    public function addEvent($data)
    {
        // Map data and unset unused fields
        $data = $this->mapEventDataToEventTableData($data);
        unset($data[':event_id']);
        self::$database->execute(
            "INSERT INTO events VALUES (
            DEFAULT, :creator_id, DEFAULT, :title, :description, :location, :date, :time,
                  :visibility, :maximum_attendees, :price);",
            $data
        );
    }

    /**
     * Update an event of the database
     * @param $data * Data of the event
     */
    public function updateEvent($data)
    {
        // Map data and unset unused fields
        $data = $this->mapEventDataToEventTableData($data);
        unset($data[':creator_id']);
        unset($data[':visibility']);
        self::$database->execute(
            "UPDATE events
            SET title = :title, description = :description, location = :location, date = :date, time = :time,
                  maximum_attendees = :maximum_attendees, price = :price
            WHERE event_id = :event_id;",
            $data
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
     * Delete an event by the id
     * @param $event_id * Id of the event
     * @return bool * successful/not successful
     */
    public function deleteEventById($event_id)
    {
        return self::$database->execute(
            "DELETE FROM events WHERE event_id = :event_id",
            [":event_id" => $event_id]
        );
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
            ":event_id" => $data['event_id'],
            ":creator_id" => $data['creator_id'],
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
}
