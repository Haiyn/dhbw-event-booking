<?php

namespace models;

use components\database\Database;
use Utility;

class User
{
    private static $_instance;
    private static $_database;

    public function __construct()
    {
        self::$_instance = $this;
        self::$_database = Database::newInstance(null);
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function newInstance()
    {
        return new self();
    }

    public function getUserByUsername($username) {
        $users = self::$_database->fetch(
            "SELECT * from users WHERE username = :username",
            [":username" => $username]
        );
        if (empty($users)) return [];
        return $users[0];
    }

    public function getUserByEmail($email) {
        $users = self::$_database->fetch(
            "SELECT * from users WHERE email = :email",
            [":email" => $email]
        );
        if (empty($users)) return [];
        return $users[0];
    }

    public function addUser($user_data) {
        self::$_database->execute(
            "INSERT INTO users VALUES (
                uuid_generate_v4(), :username, :email, :password, :first_name, :last_name, :age, :verification_hash, false, NOW())",
            $this->_mapRegisterDataToUserTableData($user_data)
        );
    }

    private function _mapRegisterDataToUserTableData($user_data) {
        // Format the date from user_data to match the database fields
        // Creation date and verified are omitted from this mapping
        return $data = [
            ":username" => $user_data['username'],
            ":email" => $user_data['email'],
            // Hash the password with the salt from config.ini.php
            ":password" => md5(AUTH_SALT . $user_data["password"]),
            ":first_name" => $user_data['first_name'],
            ":last_name" => $user_data['last_name'],
            ":age" => $user_data['age'],
            ":verification_hash" => bin2hex(openssl_random_pseudo_bytes(16)),
        ];
    }

}
