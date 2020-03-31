<?php

namespace components\database;

use components\core\Utility;
use PDO;
use PDOException;

class Database
{

    private static $instance;
    private static $connection;

    public function __construct($options)
    {
        self::$instance = $this;
        $this->openConnection($options);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(null);
        }
        return self::$instance;
    }

    public static function newInstance($options)
    {
        return new self($options);
    }

    /**
     * Open the database connection with the credentials from config.ini.php
     */
    private function openConnection($options)
    {
        // fetch all results as an object by default
        if (empty($options)) {
            $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);
        }

        try {
            $ini = Utility::getIniFile();
            self::$connection = new PDO(
                $ini['DB_TYPE'] . ':host=' . $ini['DB_HOST']  . ';port=' . $ini['DB_PORT']  . ';dbname=' .
                $ini['DB_NAME'],
                $ini['DB_USER'],
                $ini['DB_PASS'],
                $options
            );
        } catch (PDOException $exception) {
            // Connection to the database failed, redirect to error page to not expose stack trace
            header("Location: /internal-error");
            return;
        }
    }

    /*
     * Query and return all fetched data
     */
    public function fetch($query, $data)
    {
        $result = self::$connection->prepare($query);
        foreach ($data as $key => &$value) {
            $result->bindParam($key, $value);
        }
        $result->execute();
        return $result->fetchAll();
    }

    /*
     * Query and return result
     */
    public function execute($query, $data)
    {
        $result = self::$connection->prepare($query);
        foreach ($data as $key => &$value) {
            $result->bindParam($key, $value);
        }
        return $result->execute();
    }
}
