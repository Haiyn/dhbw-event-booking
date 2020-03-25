<?php

namespace components\database;

use PDO;

class Database
{

    private static $connection;

    public function __construct()
    {
        $this->openConnection();
    }

    /**
     * Open the database connection with the credentials from config.php
     */
    private function openConnection()
    {
        if(empty(self::$connection)) {
            // fetch all results as an object by default
            $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

            // TODO: Put a try in here and reroute to internal server error page if unsuccesful
            // open the connectin as PDO object
            self::$connection = new PDO(
                DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME,
                DB_USER,
                DB_PASS,
                $options
            );
        }
    }

    /*
     * Query and return all fetched data
     */
    public function fetch($query, $data) {
        $result = self::$connection->prepare($query);
        foreach ($data as &$element) {
            $result->bindParam(":{$element}", $element);
        }
        $result->execute();
        return $result->fetchAll();
    }

    /*
     * Query and return result
     */
    public function execute($query, $data) {
        $result = self::$connection->prepare($query);
        foreach ($data as &$element) {
            $result->bindParam(":{$element}", $element);
        }
        return $result->execute();
    }
}