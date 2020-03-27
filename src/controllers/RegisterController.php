<?php

namespace controllers;

use components\core\Utility;
use components\email\EmailService;
use models\User;

class RegisterController extends Controller
{
    public function render($parameters)
    {
        session_start();
        if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"]))
        {
            // Sanitize the data by removing any harmful code and markup
            $user_data = [
                "username" => filter_var(htmlspecialchars($_POST["username"]), FILTER_SANITIZE_STRING),
                "password" => htmlspecialchars($_POST["password"]),
                "email" => filter_var(htmlspecialchars($_POST["email"]), FILTER_SANITIZE_EMAIL),
                "first_name" => filter_var(htmlspecialchars($_POST["first_name"]), FILTER_SANITIZE_STRING),
                "last_name" => filter_var(htmlspecialchars($_POST["last_name"]), FILTER_SANITIZE_STRING),
                "age" => filter_var(htmlspecialchars($_POST["age"]), FILTER_SANITIZE_NUMBER_INT)
            ];
            // Trim every value to assert that no whitespaces are submitted
            foreach ($user_data as $key => &$value)
            {
                $user_data[$key] = trim($value);
            }

            $this->validateData($user_data);

            $this->registerUser($user_data);

            $this->setSuccess("You have been successfully registered to the website! 
                    Please confirm your email address with the link you've received at 
                    <strong>{$user_data['email']}</strong>");
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
        // If the sanitized required values are empty
        if (empty($data['username']) || empty($data['email']) || empty($data['password']))
        {
            $this->setError("Please enter something valid for the required fields!");
        }

        // Check if the username contains white spaces
        if (preg_match('/\s/',$data['username']))
        {
            $this->setError("Your username cannot contain whitespaces!");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        {
            $this->setError("Please enter a valid E-Mail address!");
        }

        if (!empty($data['age']) && !filter_var($data['age'], FILTER_VALIDATE_INT))
        {
            $this->setError("Please enter a valid age!");
        }
    }

    /*
     * Tries to register the user
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
        }

        // Check if email is already in database
        $existingUser = $user->getUserByEmail($user_data["email"]);
        if (!empty($existingUser))
        {
            $this->setError("An account with this E-Mail is already registered!");
        }

        // Add the user to the database
        if(!$user->addUser($user_data))
        {
            $this->setError("Sorry, something went wrong while creating your user! Please try again.");
        }

        $hash = $user->getUserByUsername($user_data['username'])->verification_hash;

        // TODO: Remove this when SMTP Server available
        $this->setSuccess("You have been successfully registered to the website! 
                    Please confirm your email address with this link: <a href='/confirm?hash={$hash}'>Confirm</a>");

        // Send a verification email to the email address
        // TODO: Uncomment when SMTP Server available
        /*$emailService = EmailService::getInstance();
        $url = Utility::getIniFile()['URL'];
        $emailService->sendEmail($user_data['email'],s
            "Confirm your email address",
            "Follow <a href='{$url}/confirm?hash={$hash}'>this link</a> to confirm your email address.");*/
    }

}