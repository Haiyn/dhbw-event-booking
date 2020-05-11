<?php

namespace components\validators;

use components\core\Utility;

/**
 * Class UserValidator
 * Validates all user input for user functions. Throws ValidatorError if validation fails
 * @package components\validators
 */
class UserValidator
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
     * @throws ValidatorException * if invalid data detected
     */
    public function validateRegisterData($data)
    {
        // If the sanitized required values are empty
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            throw new ValidatorException("Please enter something valid for the required fields!");
        }

        // Check if the username contains white spaces
        if (preg_match('/\s/', $data['username'])) {
            throw new ValidatorException("Your username cannot contain whitespaces!");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidatorException("Please enter a valid E-Mail address!");
        }

        if (!empty($data['age']) && !filter_var($data['age'], FILTER_VALIDATE_INT)) {
            throw new ValidatorException("Please enter a valid age!");
        }

        // Check if maxlength is exceeded
        if (strlen($data["username"]) > 32) {
            throw new ValidatorException("Length of username cannot exceed max length of 32.");
        }
        if (strlen($data["email"]) > 32) {
            throw new ValidatorException("Length of email cannot exceed max length of 32.");
        }
        if (strlen($data["password"]) > 32) {
            throw new ValidatorException("Length of password cannot exceed max length of 32.");
        }
        if (strlen($data["first_name"]) > 32) {
            throw new ValidatorException("Length of first_name cannot exceed max length of 32.");
        }
        if (strlen($data["last_name"]) > 32) {
            throw new ValidatorException("Length of last_name cannot exceed max length of 32.");
        }
    }

    /**
     * Checks if all the form data is logically correct:
     * Was user found, is he verified, was the correct password entered?
     * @param $user_data * data array to validate
     * @throws ValidatorException * if data invalid
     */
    public function validateLoginData($user_data)
    {
        if (empty($user_data['foundUser'])) {
            throw new ValidatorException("Invalid Username or Email!");
        }

        if (!password_verify($user_data['password'], $user_data['foundUser']->password)) {
            throw new ValidatorException("Invalid password!");
        }

        if (!$user_data['foundUser']->verified) {
            throw new ValidatorException("Please confirm your email address. Follow
                        <a href='/register?verify={$user_data['foundUser']->email}'> to confirm</a>.");
        }
    }


    public function validateUsername($new_data, $old_data)
    {
        if (empty($new_data['username'])){
            $new_data['username'] = $old_data->username;
        }
        // Check if the username contains white spaces
        if (preg_match('/\s/', $new_data['username'])) {
            throw new ValidatorException("Your username cannot contain whitespaces!");
        }
        //Check if username is already taken
        if ($new_data['username'] === $old_data->username) {
            throw new ValidatorException("This username is already taken!");
        }
        // Check if maxlength is exceeded
        if (strlen($new_data["username"]) > 32) {
            throw new ValidatorException("Length of username cannot exceed max length of 32.");
        }

    }



    /**
     * Checks if all the form data is in a valid format
     * @param $new_data * new data
     * @param $old_data * existing data
     * @throws ValidatorException
     */
    public function validateNewData($new_data, $old_data)
    {
        // Check if maxlength is exceeded
        if (strlen($new_data["first_name"]) > 32) {
            throw new ValidatorException("Length of first_name cannot exceed max length of 32.");
        }
        if (strlen($new_data["last_name"]) > 32) {
            throw new ValidatorException("Length of last_name cannot exceed max length of 32.");
        }
        if (strlen($new_data["email"]) > 32) {
            throw new ValidatorException("Length of email cannot exceed max length of 32.");
        }

        //Check if a user with given email address already exists
        if ($new_data['email'] === $old_data->email) {
            throw new ValidatorException("A user with this email already exists!");
        }
    }


}
