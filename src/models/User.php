<?php

namespace models;

use components\core\Utility;
use components\database\DatabaseService;

/**
 * Class User
 * Database model for the users table. Includes all needed queries.
 * @package models
 */
class User
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
     * Searches the users table for a user with the passed user id
     * @param $user_id * user id to search for
     * @return array|object * found users
     */
    public function getUserById($user_id)
    {
        $users = self::$database->fetch(
            "SELECT * from users WHERE user_id = :user_id",
            [":user_id" => $user_id]
        );
        if (empty($users)) {
            return [];
        }
        return $users[0];
    }

    /**
     * Searches the users table for a user with the passed username
     * @param $username * username to search for
     * @return array|object * found users
     */
    public function getUserByUsername($username)
    {
        $users = self::$database->fetch(
            "SELECT * from users WHERE username = :username",
            [":username" => $username]
        );
        if (empty($users)) {
            return [];
        }
        return $users[0];
    }

    /**
     * Searches the users table for a user with the passed email
     * @param $email * email to search for
     * @return array|object * found users
     */
    public function getUserByEmail($email)
    {
        $users = self::$database->fetch(
            "SELECT * from users WHERE email = :email",
            [":email" => $email]
        );
        if (empty($users)) {
            return [];
        }
        return $users[0];
    }

    /**
     * Adds a new user to the users table
     * @param $user_data * data needed by database
     * @return bool * successful/not successful
     */
    public function addUser($user_data)
    {
        return self::$database->execute(
            "INSERT INTO users VALUES (DEFAULT, :username, :email, :password, :first_name, :last_name, :age, 
                          :verification_hash, :verified, DEFAULT)",
            $this->mapRegisterDataToUserTableData($user_data)
        );
    }

    /**
     * Sets the verified field of the user to true when the email was verified and generates a new verification hash
     * Prevents reusing of the old hash when switching to a new email
     * @param $hash * verification hash of the user
     * @return bool * false on user for hash not found, true on update successful
     */
    public function confirmUser($hash)
    {
        // Check if the user to that hash exists in database
        // content of the user is not relevant so we check just for existence
        $exists = self::$database->fetch(
            "SELECT 1 FROM users WHERE verification_hash = :hash",
            [":hash" => $hash]
        );
        if (!$exists) {
            return false;
        }

        // If user exists for hash, update it with new hash so old one cant be reused
        return self::$database->execute(
            "UPDATE users 
                    SET verified = true, verification_hash = :new_hash
                    WHERE verification_hash = :hash",
            [
                ":hash" => $hash,
                ":new_hash" => Utility::generateSSLHash(16)
            ]
        );
    }

    /**
     * Update user data in the database
     * @param $data * Data of user
     * @return bool
     */
    public function updateUserData($data)
    {
        return self::$database->execute(
            "UPDATE users
            SET first_name = :first_name, last_name = :last_name, email = :email, username = :username
            WHERE user_id = :user_id",
            $this->mapUpdatedDataToUserTableData($data)
        );
    }

    /**
     * Update the verified state when the email is being updated
     * @param $email * Email of the user
     * @param $hash * New hash
     * @param $verified * New verified state
     * @return bool * Successful/ not successful
     */
    public function updateUserVerified($email, $hash, $verified)
    {
        return self::$database->execute(
            "UPDATE users
            SET verification_hash = :hash, verified = :verified
            WHERE email = :email",
            [":email" => $email, ":hash" => $hash, ":verified" => $verified]
        );
    }

    /**
     * Maps the updated user data into the database
     * @param $data * data to map
     * @return array * mapped data that fits users table data
     */
    private function mapUpdatedDataToUserTableData($data)
    {
        if (empty($data['first_name'])) {
            $data['first_name'] = null;
        }
        if (empty($data['last_name'])) {
            $data['last_name'] = null;
        }

        return $data = [
            ":first_name" => $data['first_name'],
            ":last_name" => $data['last_name'],
            ":email" => $data['email'],
            ":username" => $data['username'],
            ":user_id" => $data['user_id']
        ];
    }

    /**
     * Update user password in the database
     * @param $data * new password
     * @param $hash
     * @return bool
     */
    public function updatePassword($data, $hash)
    {
        $exists = self::$database->fetch(
            "SELECT 1 FROM users WHERE verification_hash = :hash",
            [":hash" => $hash]
        );
        if ($exists) {
            self::$database->execute(
                "UPDATE users
        SET password = :password
        WHERE verification_hash = :verification_hash",
                [":password" => Utility::encryptPassword($data['password']),
                    ":verification_hash" => $hash]
            );

            return self::$database->execute(
                "UPDATE users 
                    SET verification_hash = :new_hash
                    WHERE verification_hash = :hash",
                [
                    ":hash" => $hash,
                    ":new_hash" => Utility::generateSSLHash(16)
                ]
            );
        }
    }

    /**
     * Maps the data from user_data to a users database object
     * user_id and creation_date are generated in database
     * @param $user_data * data to map
     * @return array * mapped data that fits users table data
     */
    private function mapRegisterDataToUserTableData($user_data)
    {
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
            ":password" => Utility::encryptPassword($user_data['password']),
            ":first_name" => $user_data['first_name'],
            ":last_name" => $user_data['last_name'],
            ":age" => $user_data['age'],
            ":verification_hash" => Utility::generateSSLHash(16),
            ":verified" => "false"
        ];
    }
}
