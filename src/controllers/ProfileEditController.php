<?php


namespace controllers;

use components\core\Utility;
use components\email\EmailService;
use components\validators\ValidatorException;
use components\validators\UserValidator;
use models\User;

class ProfileEditController extends Controller
{
    public function render($params)
    {
        $this->session->checkSession();
        $user = User::getInstance();
        $userId = $user->getUserById($_SESSION['USER_ID']);
        $email = $userId->email;

        $this->displayUserInfo();

        if (isset($_POST["username"])) {
            $new_data = [
                'username' => filter_var(htmlspecialchars($_POST['username']), FILTER_SANITIZE_STRING)
            ];
            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }

            $this->updateUsername($new_data, $userId);
            $this->setSuccess("Profile successfully updated");

        }


        //Save button pressed on personal info
        if (isset($_POST["first_name"]) && isset($_POST["last_name"]) && isset($_POST["email"])) {

            $new_data = [
                'first_name' => filter_var(htmlspecialchars($_POST['first_name']), FILTER_SANITIZE_STRING),
                'last_name' => filter_var(htmlspecialchars($_POST['last_name']), FILTER_SANITIZE_STRING),
                'email' => filter_var(htmlspecialchars($_POST['email']), FILTER_SANITIZE_EMAIL)
            ];

            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }

            $this->updatePersonalInfo($new_data, $userId);
            $this->generateEmailConfirmation($email);
            $this->setSuccess("Profile successfully updated");

        }
    }


    /**
     * Displays personal information of currently logged in user inside editing input fields
     */
    private function displayUserInfo()
    {
        $user = User::getInstance();
        $userById = $user->getUserById($_SESSION['USER_ID']);

        $username = $userById->username;
        $firstName = $userById->first_name;
        $lastName = $userById->last_name;
        $email = $userById->email;

        $this->view->username = $username;
        $this->view->firstName = $firstName;
        $this->view->lastName = $lastName;
        $this->view->email = $email;
    }

    /**
     * Updates username
     * @param $new_data *new username
     * @param $old_data *old username
     */
    private function updateUsername($new_data, $old_data)
    {
        $user = User::getInstance();
        $userId = $_SESSION['USER_ID'];

        $userValidator = UserValidator::getInstance();
        try {
            $userValidator->validateUsername($new_data, $old_data);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage());
        }

        $new_data += ["user_id" => $userId];

        if (!$user->updateUsername($new_data)) {
            $this->setError("Something went wrong");
        }
    }

    /**
     * Updates first name, last name or email
     * @param $new_data * new data to be saved to database
     * @param $old_data *existing data
     */
    private function updatePersonalInfo($new_data, $old_data)
    {
        $user = User::getInstance();
        $userId = $_SESSION['USER_ID'];

        $userValidator = UserValidator::getInstance();
        try {
            $userValidator->validateNewData($new_data, $old_data);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage());
        }

        $new_data += ["user_id" => $userId];

        if (!$user->updateUserData($new_data)) {
            $this->setError("Something went wrong");
        }
    }

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
            $this->setSuccess("Please confirm your email address with this link: <a href='/confirm?hash={$hash}'>Confirm</a>");
        }
    }
}

