<?php

namespace models;

use components\database\DatabaseService;

/**
 * Class Session
 * Database model for the sessions table. Includes all needed queries.
 * @package models
 */
class Session
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
     * Searches the sessions table for an entry with session_id as primary key
     * @param $session_id * session id to search for
     * @return array * found session
     */
    public function getSessionBySessionId($session_id)
    {
        $session = self::$database->fetch(
            "SELECT * from sessions WHERE session_id = :session_id",
            [":session_id" => $session_id]
        );
        if (empty($session)) {
            return [];
        }
        return $session[0];
    }

    /**
     * Searches the sessions table for an entry with user_id parameter as user_id column value
     * @param $user_id * user id to search for
     * @return array * found session
     */
    public function getSessionByUserId($user_id)
    {
        $session = self::$database->fetch(
            "SELECT * from sessions WHERE user_id = :user_id",
            [":user_id" => $user_id]
        );
        if (empty($session)) {
            return [];
        }
        return $session[0];
    }

    /**
     * Saves the passed data to the sessions table
     * If the session already exists, the existing entry is updated to prevent duplicates
     * @param $session_data * all needed data to update an existing/insert a new session
     * @return mixed * successful/not successful
     */
    public function saveSession($session_data)
    {
        // Check if there's already an entry for the passed session_id
        if (empty($this->getSessionBySessionId($session_data['session_id']))) {
            // No session with this ID exists yet, insert it
            $query = "INSERT INTO sessions VALUES (:session_id, :user_id, :ip_address, :user_agent)";
        } else {
            // Session ID already exists in database, update the existing entry
            $query = "UPDATE sessions
                SET user_id = :user_id, ip_address = :ip_address, user_agent = :user_agent
                WHERE session_id = :session_id";
        }

        return self::$database->execute(
            $query,
            $this->mapCurrentSessionToSessionsTableData($session_data)
        );
    }

    /**
     * Deletes an entry from the sessions table based on the passed session_id
     * @param $session_id * primary key in the database
     * @return mixed * successful/not successful
     */
    public function deleteSessionById($session_id)
    {
        return self::$database->execute(
            "DELETE FROM sessions WHERE session_id = :session_id",
            ["session_id" => $session_id]
        );
    }

    /**
     * Maps the passed data into a parametrized array that can be passed to the database
     * @param $session_data * data array
     * @return array * parametrized array
     */
    public function mapCurrentSessionToSessionsTableData($session_data)
    {
        // Ensure that no empty string values in optional fields enter the database
        if (empty($session_data['user_id'])) {
            $session_data['user_id'] = null;
        }

        return $session_data = [
            ":session_id" => $session_data['session_id'],
            ":user_id" => $session_data['user_id'],
            ":ip_address" => $session_data['ip_address'],
            ":user_agent" => $session_data['user_agent']
        ];
    }
}
