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

        // /register?verify=[email] was called to resend a verification link
        if (isset($_GET['verify'])) {
            $email = filter_var(htmlspecialchars($_GET['verify']), FILTER_SANITIZE_EMAIL);

            // if the sanitized value is still a valid email, generate the link
            // otherwise show an error message
            if (filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $this->generateEmailConfirmation($email);

                $this->setSuccess("The E-Mail confirmation link has been successfully sent to 
                    <strong>{$email}</strong>");
            }
            else {
                $this->setError("Sorry, we cannot a send a verification link to this email!");
            }
        }

        // The register button was pressed
        if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']))
        {
            // Sanitize the data by removing any harmful code and markup
            $user_data = [
                'username' => filter_var(htmlspecialchars($_POST['username']), FILTER_SANITIZE_STRING),
                'password' => htmlspecialchars($_POST['password']),
                'email' => filter_var(htmlspecialchars($_POST['email']), FILTER_SANITIZE_EMAIL),
                'first_name' => filter_var(htmlspecialchars($_POST['first_name']), FILTER_SANITIZE_STRING),
                'last_name' => filter_var(htmlspecialchars($_POST['last_name']), FILTER_SANITIZE_STRING),
                'age' => filter_var(htmlspecialchars($_POST['age']), FILTER_SANITIZE_NUMBER_INT)
            ];
            // Trim every value to assert that no whitespaces are submitted
            foreach ($user_data as $key => &$value) {
                $user_data[$key] = trim($value);
            }

            $this->validateData($user_data);

            $this->registerUser($user_data);

            $this->generateEmailConfirmation($user_data['email']);

            $this->setSuccess("You have been successfully registered to the website! 
                    Please confirm your email address with the link you've received at 
                    <strong>{$user_data['email']}</strong>");
        }

        $this->view->pageTitle = "Register";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }

    /**
     * Checks if all the form data is in a valid format.
     * Redirects with an error if something is wrong with the data.
     * @param $data * data array to validate
     */
    private function validateData($data)
    {
        // If the sanitized required values are empty
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $this->setError("Please enter something valid for the required fields!");
        }

        // Check if the username contains white spaces
        if (preg_match('/\s/', $data['username'])) {
            $this->setError("Your username cannot contain whitespaces!");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->setError("Please enter a valid E-Mail address!");
        }

        if (!empty($data['age']) && !filter_var($data['age'], FILTER_VALIDATE_INT)) {
            $this->setError("Please enter a valid age!");
        }

        // Check if maxlength is exceeded
        if (strlen($data["username"]) > 32) {
            $this->setError("Length of username cannot exceed max length of 32.");
        }
        if (strlen($data["email"]) > 32) {
            $this->setError("Length of email cannot exceed max length of 32.");
        }
        if (strlen($data["password"]) > 32) {
            $this->setError("Length of password cannot exceed max length of 32.");
        }
        if (strlen($data["first_name"]) > 32) {
            $this->setError("Length of first_name cannot exceed max length of 32.");
        }
        if (strlen($data["last_name"]) > 32) {
            $this->setError("Length of last_name cannot exceed max length of 32.");
        }
    }

    /**
     * Tries to register the user
     * Redirects with an error if something is wrong with the data.
     * @param $user_data * data array for adding the user
     */
    private function registerUser($user_data)
    {
        $user = User::getInstance();

        // Check if username is already in database
        $existingUser = $user->getUserByUsername($user_data["username"]);
        if (!empty($existingUser)) {
            $this->setError("This username is already taken!");
        }

        // Check if email is already in database
        $existingUser = $user->getUserByEmail($user_data["email"]);
        if (!empty($existingUser)) {
            $this->setError("An account with this E-Mail is already registered!");
        }

        // Add the user to the database
        if (!$user->addUser($user_data)) {
            $this->setError("Sorry, something went wrong while creating your user! Please try again.");
        }
    }

    /*
     * Generates a confirmation link for the registered user and sends it depending on ini settings
     */
    private function generateEmailConfirmation($email)
    {
        $user = User::getInstance();

        $hash = $user->getUserByEmail($email)->verification_hash;

        if (empty($hash))
        {
            $this->setError("Sorry, the user to the email <strong>{$email}</strong> does not exist!");
        }

        // Check if Email Sending is enabled
        if(filter_var(Utility::getIniFile()['EMAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN))
        {
            // Send a verification email to the email address
            $emailService = EmailService::getInstance();
            $url = Utility::getIniFile()['URL'];
            $emailService->sendEmail($email,
                "Confirm your email address",
                "Follow <a href='{$url}/confirm?hash={$hash}'>this link</a> to confirm your email address.");
        }
        else
        {
            // Display the verification link in the browser for testing
            $this->setSuccess("You have been successfully registered to the website! 
                    Please confirm your email address with this link: <a href='/confirm?hash={$hash}'>Confirm</a>");
        }
    }
}
