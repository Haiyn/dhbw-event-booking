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

        $this->displayUserInfo();

        //Save button pressed on personal info
        if (isset($_POST["username"]) && isset($_POST["first_name"]) && isset($_POST["last_name"]) && isset($_POST["email"])) {

            $new_data = [
                'username' => filter_var(htmlspecialchars($_POST['username']), FILTER_SANITIZE_STRING),
                'first_name' => filter_var(htmlspecialchars($_POST['first_name']), FILTER_SANITIZE_STRING),
                'last_name' => filter_var(htmlspecialchars($_POST['last_name']), FILTER_SANITIZE_STRING),
                'email' => filter_var(htmlspecialchars($_POST['email']), FILTER_SANITIZE_EMAIL)
            ];

            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }

            $this->updatePersonalInfo($new_data, $userId);
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
     * Updates username, first name, last name or email
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
        } catch (ControllerException $exception) {
            $this->setError($exception->getMessage());
        }

        $new_data += ["user_id" => $userId];

        if (!$user->updateUserData($new_data)) {
            $this->setError("Something went wrong");
        }
    }
}

