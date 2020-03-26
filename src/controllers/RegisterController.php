<?php

namespace controllers;

use models\User;

class RegisterController extends Controller
{
    public function render($parameters)
    {
        session_start();
        if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"]))
        {
            $user_data = [
                "username" => htmlspecialchars($_POST["username"]),
                "password" => htmlspecialchars($_POST["password"]),
                "email" => filter_var(htmlspecialchars($_POST["email"]), FILTER_SANITIZE_EMAIL),
                "first_name" => htmlspecialchars($_POST["first_name"]),
                "last_name" => htmlspecialchars($_POST["last_name"]),
                "age" => filter_var(htmlspecialchars($_POST["age"]), FILTER_SANITIZE_NUMBER_INT)
            ];

            if(!$this->validateData($user_data))
            {
                return;
            }

            $this->registerUser($user_data);

            $this->setSuccess("You have been successfully registered to the website! 
                    Please confirm your email address with the link you've received at {$user_data['email']}");
        }

        $this->view->pageTitle = "Register";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }

    /*
     * Checks if all the form data is in a valid format.
     * Redirects with an error if something is wrong with the data.
     */
    private function validateData($data)
    {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        {
            $this->setError("Please enter a valid E-Mail address!");
            return false;
        }

        if (!empty($data['age']) && !filter_var($data['age'], FILTER_VALIDATE_INT))
        {
            $this->setError("Please enter a valid age!");
            return false;
        }

        return true;
    }

    /*
     * Tries to register the user.
     * Redirects with an error if something is wrong with the data.
     */
    private function registerUser($user_data)
    {
        $user = User::newInstance();

        // Check if username is already in database
        $existingUser = $user->getUserByUsername($user_data["username"]);
        if (!empty($existingUser))
        {
            $this->setError("This username is already taken!");
            return;
        }

        // Check if email is already in database
        $existingUser = $user->getUserByEmail($user_data["email"]);
        if (!empty($existingUser))
        {
            $this->setSuccess("An account with this E-Mail is already registered!");
            return;
        }

        // Add the user to the database
        $user->addUser($user_data);
    }

}