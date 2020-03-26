<?php

namespace models;

use components\database\Database;

class User
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

    /*
     * Searches the users table for a user with the passed user id
     */
    public function getUserById($user_id) {
        $users = self::$_database->fetch(
            "SELECT * from users WHERE username = :username",
            [":user_id" => $user_id]
        );
        if (empty($users)) return [];
        return $users[0];
    }

    /*
     * Searches the users table for a user with the passed username
     */
    public function getUserByUsername($username) {
        $users = self::$database->fetch(
            "SELECT * from users WHERE username = :username",
            [":username" => $username]
        );
        if (empty($users))
        {
            return [];
        }
        return $users[0];
    }

    /*
     * Searches the users table for a user with the passed email
     */
    public function getUserByEmail($email) {
        $users = self::$database->fetch(
            "SELECT * from users WHERE email = :email",
            [":email" => $email]
        );
        if (empty($users))
        {
            return [];
        }
        return $users[0];
    }

    /*
     * Adds a new user to the users table
     */
    public function addUser($user_data) {
        self::$database->execute(
            "INSERT INTO users VALUES (DEFAULT, :username, :email, :password, :first_name, :last_name, :age, :verification_hash, :verified, DEFAULT)",
            $this->mapRegisterDataToUserTableData($user_data)
        );
    }

    /*
     * Maps the data from user_data to a users database object
     * user_id and creation_date are generated in database
     */
    private function mapRegisterDataToUserTableData($user_data) {
        $ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/config.ini.php");

        // Check for empty values, postgres must receive null not ""
        if (empty($user_data['first_name'])) {
            $user_data['first_name'] = null;
        }
        if (empty($user_data['last_name'])) {
            $user_data['last_name'] = null;
        }
        if (empty($user_data['age'])) {
            $user_data['age'] = null;
        }

        return $data = [
            ":username" => $user_data['username'],
            ":email" => $user_data['email'],
            // Hash the password with the salt from config.ini.php
            ":password" => md5($ini['AUTH_SALT'] . $user_data["password"]),
            ":first_name" => $user_data['first_name'],
            ":last_name" => $user_data['last_name'],
            ":age" => $user_data['age'],
            ":verification_hash" => bin2hex(openssl_random_pseudo_bytes(16)),
            ":verified" => "false"
        ];
    }

}
