<?php

namespace controllers;

use components\core\Utility;
use components\email\EmailService;
use components\validators\UserValidator;
use components\validators\ValidatorException;
use models\User;

/**
 * Class RegisterController
 * Controls registering of users with email verification.
 * @package controllers
 */
class RegisterController extends Controller
{
    public function render()
    {
        // /register?verify=[email] was called to resend a verification link
        if (isset($_GET['verify'])) {
            $email = filter_var(htmlspecialchars($_GET['verify']), FILTER_SANITIZE_EMAIL);

            // if the sanitized value is still a valid email, generate the link
            // otherwise show an error message
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->generateEmailConfirmation($email);

                $this->setSuccess("The E-Mail confirmation link has been successfully sent to 
                    <strong>{$email}</strong>");
            } else {
                $this->setError("Sorry, we cannot a send a verification link to this email!");
            }
        }

        // The register button was pressed
        if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
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

            // Validate the data with the User Validator
            $userValidator = UserValidator::getInstance();
            try {
                $userValidator->validateRegisterData($user_data);
            } catch (ValidatorException $exception) {
                $this->setError($exception->getMessage());
            }

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

    /**
     * Generates a confirmation link for the registered user and sends it depending on ini settings
     * @param $email
     */
    private function generateEmailConfirmation($email)
    {
        $user = User::getInstance();
        $hash = $user->getUserByEmail($email)->verification_hash;

        // If the hash is empty, the user does not exist
        // prevents abusing /verify to verify an account with another email
        if (empty($hash)) {
            $this->setError("Sorry, there is no user associated with the email <strong>{$email}</strong>!");
        }

        // Check if Email Sending is enabled
        if (filter_var(Utility::getIniFile()['EMAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            // Send a verification email to the email address
            $emailService = EmailService::getInstance();
            $url = Utility::getApplicationURL();
            $emailService->sendEmail(
                $email,
                "Confirm your email address",
                "Follow <a href='" . Utility::getApplicationURL() . "/confirm?hash={$hash}'>this link</a> 
                to confirm your email address."
            );
        } else {
            // Display the verification link in the browser for testing
            $this->setSuccess("You have been successfully registered to the website! 
                    Please confirm your email address with this link: <a href='/confirm?hash={$hash}'>Confirm</a>");
        }
    }
}
