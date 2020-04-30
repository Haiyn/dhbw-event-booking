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
//TODO REMOVE THIS
        //Save button pressed on password change
        if (isset($_POST["password"])) {
            $new_data = [
                'password' => htmlspecialchars($_POST['password'])
            ];

            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }
            $this->updatePassword($new_data, $userId);
            $this->setSuccess("Please verify your new password");
        }

        $this->view->pageTitle = "Edit Profile";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);

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



//   TODO REMOVE THIS
    /**
     *Updates password after checking if new password and repeated password match and requires
     * user to confirm new password via email
     * @param $new_data
     * @param $old_data
     */
    private
    function updatePassword($new_data, $old_data)
    {
        $user = User::getInstance();
        $userId = $_SESSION['USER_ID'];

        $userValidator = UserValidator::getInstance();
        try {
            $userValidator->validateNewPassword($new_data, $old_data);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage());
        }

        $new_data += ["user_id" => $userId];
        $email = $user->getUserByEmail($userId->email);

        //TODO doesn't send email
        if ($user->updatePassword($new_data)) {
            $this->confirmationEmail($user, $email);
        }

        /* if (!$user->updatePassword($new_data)) {
             $this->setError("Something went wrong");
         }*/


    }

    /**
     * Sends email to user to confirm changes
     * @param $email
     */
    private function confirmationEmail($user, $email)
    {
        $iniFile = Utility::getIniFile();

        if (filter_var($iniFile['EMAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            // Send the notification email to the email address
            $emailService = EmailService::getInstance();
            $emailService->sendEmail($user->email,
                "Your password has been updated.");
        } else {
            $this->setError(
                "Email failed to send"
            );
        }
    }


}

