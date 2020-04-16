<?php

namespace components\validators;

use components\core\ControllerException;

class UserValidation
{
    private static $instance;

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Checks if all the form data is in a valid format.
     * @param $data * data array to validate
     * @throws ControllerException * if invalid data detected
     */
    public function validateRegisterData($data)
    {
        // If the sanitized required values are empty
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            throw new ControllerException("Please enter something valid for the required fields!");
        }

        // Check if the username contains white spaces
        if (preg_match('/\s/', $data['username'])) {
            throw new ControllerException("Your username cannot contain whitespaces!");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ControllerException("Please enter a valid E-Mail address!");
        }

        if (!empty($data['age']) && !filter_var($data['age'], FILTER_VALIDATE_INT)) {
            throw new ControllerException("Please enter a valid age!");
        }

        // Check if maxlength is exceeded
        if (strlen($data["username"]) > 32) {
            throw new ControllerException("Length of username cannot exceed max length of 32.");
        }
        if (strlen($data["email"]) > 32) {
            throw new ControllerException("Length of email cannot exceed max length of 32.");
        }
        if (strlen($data["password"]) > 32) {
            throw new ControllerException("Length of password cannot exceed max length of 32.");
        }
        if (strlen($data["first_name"]) > 32) {
            throw new ControllerException("Length of first_name cannot exceed max length of 32.");
        }
        if (strlen($data["last_name"]) > 32) {
            throw new ControllerException("Length of last_name cannot exceed max length of 32.");
        }
    }

    /**
     * Checks if all the form data is logically correct:
     * Was user found, is he verified, was the correct password entered?
     * @param $user_data * data array to validate
     * @throws ControllerException * if data invalid
     */
    public function validateLoginData($user_data) {
        if (empty($user_data['foundUser'])) {
            throw new ControllerException("Invalid Username or Email!");
        }

        if($user_data['passwordHash'] != $user_data['foundUser']->password) {
            throw new ControllerException("Invalid password!");
        }

        if(!$user_data['foundUser']->verified) {
            throw new ControllerException("Please confirm your email address. Follow
                        <a href='/register?verify={$user_data['foundUser']->email}'> to confirm</a>.");
        }
    }
}
