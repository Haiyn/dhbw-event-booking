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

    public static function newInstance()
    {
        return new self();
    }

    public function addEvent($data)
    {
        self::$database->execute(
            "INSERT INTO events VALUES (
            DEFAULT, :creator_id, DEFAULT, :title, :description, :location, :date, :time,
                  :visibility, :maximum_attendees, :price);",
            $this->mapEventDataToUserTableData($data)
        );
    }

    private function mapEventDataToUserTableData($data)
    {
        return $data = [
            ":creator_id" => $data['creator_id'],
            ":title" => $data["title"],
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
