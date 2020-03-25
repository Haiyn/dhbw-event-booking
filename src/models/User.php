<?php

namespace models;

use components\database\Database;

class User
{
    private static $_database;

    public function __construct()
    {
    }

    public function getUserByUsername($username) {
        $users = $_database->fetch(
            "SELECT * from Users WHERE username = :username",
            ["username" => $username]
        );
        if (empty($users)) return [];
        return $users[0];
    }

    public function getUserByEmail($email) {
        $users = $_database->fetch(
            "SELECT * from Users WHERE {$email} = email",
            ["email" => $email]
        );
        if (empty($users)) return [];
        return $users[0];
    }

    public function addUser($data) {

    }

}
