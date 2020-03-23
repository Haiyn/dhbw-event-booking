<?php

class Database
{

    private static $connection;
    /**
     * Open the database connection with the credentials from application/config/config.php
     */
    private function openConnection()
    {
        if(empty(self::$connection)) {
            // fetch all results as an object by default
            $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

            // open the connectin as PDO object
            self::$connection = new PDO(
                DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME,
                DB_USER,
                DB_PASS,
                $options
            );
        }
    }

    public function fetch($query, $data) {
        if(empty(self::$connection)) {
            $this->openConnection();
        }
        $result = self::$connection->prepare($query);
        $result->execute($data);
        return $result->fetchAll();
    }

    public function execute($query, $data) {
        $result = self::$connection->prepare($query);
        return $result->execute($data);
    }
}