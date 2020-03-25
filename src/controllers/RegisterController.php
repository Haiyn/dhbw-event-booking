<?php

namespace controllers;

use models\User;

class RegisterController extends Controller
{
    public function render($parameters)
    {
        if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"]))
        {
            $user_data = [
                "username" => htmlspecialchars($_POST["username"]),
                "password" => htmlspecialchars($_POST["password"]),
                "email" => htmlspecialchars($_POST["email"]),
                "first_name" => htmlspecialchars($_POST["first_name"]),
                "last_name" => htmlspecialchars($_POST["last_name"]),
                "age" => htmlspecialchars($_POST["age"])
            ];

            $this->_validateData($user_data);

            $this->_registerUser($user_data);

            $this->_redirect('/register?success');
        }

        $this->_view->pageTitle = "Register";
        $this->_view->isSuccess = isset($_GET["success"]);
        $this->_view->isError = isset($_GET["error"]);
    }

    /*
     * Checks if all the form data is in a valid format.
     * Redirects with an error if something is wrong with the data.
     */
    private function _validateData($data)
    {

    }

    /*
     * Tries to register the user.
     * Redirects with an error if something is wrong with the data.
     */
    private function _registerUser($user_data)
    {
        $user = new User();

        // Check if username is already in database
        //$existingUser = $user->getUserByUsername($user_data["username"]);
        if (!empty($existingUser))
        {
            $this->_setError("This username is already taken!");
            return;
        }

        // Check if email is already in database
        //$existingUser = $user->getUserByEmail($user_data["email"]);
        if (empty($existingUser))
        {
            $this->_setSuccess("An account with this E-Mail is already registered!");
            return;
        }

        // Hash the password with the salt from config.php
        $passwordHash = md5(AUTH_SALT . $user_data["password"]);
        $user_data["password"] = $passwordHash;

        // Add the user to the database
        $user->addUser($user_data);
    }

}